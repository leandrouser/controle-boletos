<?php

namespace App\Http\Controllers;

use App\Models\BeneficiarioIdentificado;
use App\Models\Boleto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BoletoController extends Controller
{

    private function parseBrValue(string $valor): float
    {
        $v = trim($valor);
        if (str_contains($v, ',')) {
            $v = str_replace('.', '', $v);
            $v = str_replace(',', '.', $v);
        }
        return (float) $v;
    }

    private function convenio48paraCodigo44(string $linha): string
    {
        return substr($linha, 0, 11)
             . substr($linha, 12, 11)
             . substr($linha, 24, 11)
             . substr($linha, 36, 11);
    }

    private function extrairContaOrigem(string $linha): ?string
    {
        $tam = strlen($linha);

        if ($tam === 48 && str_starts_with($linha, '8')) {
            $c44        = $this->convenio48paraCodigo44($linha);
            $campoLivre = substr($c44, 15);
            return substr($c44, 0, 2) . substr($campoLivre, 0, 20);
        }

        if ($tam === 47 && str_starts_with($linha, '8')) {
            $c44 = substr($linha, 0, 11)
                 . substr($linha, 12, 11)
                 . substr($linha, 24, 11)
                 . substr($linha, 36, 11);
            $campoLivre = substr($c44, 15);
            return substr($c44, 0, 2) . substr($campoLivre, 0, 20);
        }

        if ($tam === 44 && str_starts_with($linha, '8')) {
            $campoLivre = substr($linha, 15);
            return substr($linha, 0, 2) . substr($campoLivre, 0, 20);
        }

        if ($tam === 47 && !str_starts_with($linha, '8')) {
            $campoLivre = substr($linha, 4, 5)
                        . substr($linha, 11, 10)
                        . substr($linha, 22, 10);
            return substr($linha, 0, 3) . substr($campoLivre, 0, 19);
        }

        if ($tam === 44 && !str_starts_with($linha, '8')) {
            return substr($linha, 0, 3) . substr($linha, 4, 19);
        }

        return null;
    }

    public function create()
    {
        $categorias = \App\Models\Categoria::orderBy('nome')->get();
        return view('boletos.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'beneficiario'    => 'required|string|max:255',
            'categoria_id'    => 'nullable|exists:categorias,id',
            'valor'           => 'required',
            'data_vencimento' => 'required|date',
        ]);

        $codigoLimpo = $request->filled('codigo_barras')
            ? preg_replace('/[^0-9]/', '', $request->codigo_barras)
            : null;

        if ($codigoLimpo) {
            $existe = Boleto::where('codigo_barras', $codigoLimpo)->first();
            if ($existe) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Erro: Este boleto já foi cadastrado para {$existe->beneficiario}.");
            }
        }

        $userId           = Auth::id();
        $contaOrigem      = $request->input('conta_origem');
        $assinatura       = $request->input('assinatura_origem');
        $nomeBeneficiario = $request->input('beneficiario');
        $categoriaId      = $request->input('categoria_id');

        if (empty($contaOrigem) && $codigoLimpo) {
            $contaOrigem = $this->extrairContaOrigem($codigoLimpo);
        }
        if (empty($assinatura) && $contaOrigem) {
            $assinatura = $contaOrigem;
        }

        $isParcelado = $request->has('repete_boleto') && $request->has('vencimentos_parcelas');
        if ($isParcelado) {
            $vencimentos = $request->input('vencimentos_parcelas');
            $valores     = $request->input('valores_parcelas');
            $total       = count($vencimentos);

            foreach ($vencimentos as $index => $data) {
                Boleto::create([
                    'beneficiario'      => $nomeBeneficiario . " (" . ($index + 1) . "/{$total})",
                    'categoria_id'      => $categoriaId,
                    'valor'             => $this->parseBrValue($valores[$index]),
                    'data_vencimento'   => $data,
                    'codigo_barras'     => $codigoLimpo,
                    'linha_digitavel'   => $request->codigo_barras,
                    'assinatura_origem' => $assinatura,
                    'status'            => 'pendente',
                    'user_id'           => $userId,
                ]);
            }
        } else {
            Boleto::create([
                'beneficiario'      => $nomeBeneficiario,
                'categoria_id'      => $categoriaId,
                'valor'             => $this->parseBrValue($request->valor),
                'data_vencimento'   => $request->data_vencimento,
                'codigo_barras'     => $codigoLimpo,
                'assinatura_origem' => $assinatura,
                'status'            => 'pendente',
                'user_id'           => $userId,
            ]);
        }

        if (!empty($contaOrigem) && !empty($nomeBeneficiario)) {
            $assinaturaFinal = $assinatura ?? $contaOrigem;
            BeneficiarioIdentificado::updateOrCreate(
                ['assinatura' => $assinaturaFinal],
                [
                    'conta_origem'  => $contaOrigem,
                    'nome_sugerido' => $nomeBeneficiario,
                ]
            );
        }

        $mensagem = $isParcelado
            ? "Sucesso! Foram gerados {$total} boletos para {$nomeBeneficiario}."
            : "Boleto de {$nomeBeneficiario} cadastrado com sucesso!";

        return redirect()->back()->with('success', $mensagem);
    }

    public function verificarDuplicado(Request $request)
    {
        $codigo = preg_replace('/[^0-9]/', '', $request->query('codigo'));
        $boleto = Boleto::where('codigo_barras', $codigo)->first();

        return response()->json([
            'duplicado'     => !!$boleto,
            'beneficiario'  => $boleto ? $boleto->beneficiario : null,
            'data_cadastro' => $boleto ? $boleto->created_at->format('d/m/Y') : null,
        ]);
    }

    public function consultarAssinatura($assinatura)
    {
        $identificado = BeneficiarioIdentificado::where('assinatura', $assinatura)->first();

        return response()->json([
            'sucesso' => !!$identificado,
            'nome'    => $identificado ? $identificado->nome_sugerido : null,
        ]);
    }

    public function consultarConta($conta)
    {
        $identificado = BeneficiarioIdentificado::where('conta_origem', $conta)->first();

        if (!$identificado) {
            $identificado = BeneficiarioIdentificado::where('assinatura', $conta)->first();
        }

        return response()->json([
            'sucesso' => !!$identificado,
            'nome'    => $identificado ? $identificado->nome_sugerido : null,
        ]);
    }

    public function index(Request $request)
    {
        $query  = Boleto::query();
        $status = $request->get('status', 'pendente');

        if ($status === 'vence_hoje') {
            $query->where('status', 'pendente')
                  ->whereDate('data_vencimento', Carbon::today());
        } else {
            $query->where('status', $status);
        }

        if ($request->filled('beneficiario')) {
            $query->whereRaw('LOWER(beneficiario) LIKE ?', ['%' . strtolower($request->beneficiario) . '%']);
        }

        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        if ($request->filled('data_inicio') && $request->filled('data_fim')) {
            $query->whereBetween('data_vencimento', [$request->data_inicio, $request->data_fim]);
        } elseif ($request->filled('data_vencimento')) {
            $query->where('data_vencimento', $request->data_vencimento);
        }

        $boletos = $query->orderBy('data_vencimento', 'asc')->paginate(10)->withQueryString();

        $hoje      = Carbon::today();
        $fimSemana = Carbon::now()->endOfWeek();
        $fimMes    = Carbon::now()->endOfMonth();

        $totalDia    = (float) Boleto::where('status', 'pendente')->whereDate('data_vencimento', '<=', $hoje)->sum('valor');
        $qtdDia      = Boleto::where('status', 'pendente')->whereDate('data_vencimento', '<=', $hoje)->count();
        $totalSemana = (float) Boleto::where('status', 'pendente')->whereDate('data_vencimento', '<=', $fimSemana)->sum('valor');
        $qtdSemana   = Boleto::where('status', 'pendente')->whereDate('data_vencimento', '<=', $fimSemana)->count();
        $totalMes    = (float) Boleto::where('status', 'pendente')->whereDate('data_vencimento', '<=', $fimMes)->sum('valor');
        $qtdMes      = Boleto::where('status', 'pendente')->whereDate('data_vencimento', '<=', $fimMes)->count();

        $categorias = \App\Models\Categoria::orderBy('nome')->get();

        return view('boletos.index', compact(
            'boletos', 'hoje', 'status',
            'totalDia', 'totalSemana', 'totalMes',
            'qtdDia', 'qtdSemana', 'qtdMes',
            'categorias'
        ));
    }

    public function edit($id)
    {
        $boleto     = Boleto::findOrFail($id);
        $categorias = \App\Models\Categoria::orderBy('nome')->get();
        return view('boletos.edit', compact('boleto', 'categorias'));
    }

    public function update(Request $request, $id)
    {
        $boleto = Boleto::findOrFail($id);

        $request->validate([
            'beneficiario'    => 'required|string|max:255',
            'categoria_id'    => 'nullable|exists:categorias,id',
            'valor'           => 'required',
            'data_vencimento' => 'required|date',
        ]);

        $dataPagamento = $boleto->data_pagamento;
        if ($request->status == 'pago' && $boleto->status == 'pendente') {
            $dataPagamento = now();
        } elseif ($request->status == 'pendente') {
            $dataPagamento = null;
        }

        $boleto->update([
            'beneficiario'    => $request->beneficiario,
            'categoria_id'    => $request->categoria_id,
            'valor'           => $this->parseBrValue($request->valor),
            'data_vencimento' => $request->data_vencimento,
            'codigo_barras'   => $request->codigo_barras,
            'status'          => $request->status,
            'data_pagamento'  => $dataPagamento,
        ]);

        return redirect()->route('dashboard')->with('success', 'Boleto atualizado com sucesso!');
    }

    public function pagar($id)
    {
        $boleto = Boleto::findOrFail($id);
        $boleto->update(['status' => 'pago', 'data_pagamento' => now()]);
        return redirect()->back()->with('success', 'Pagamento confirmado com sucesso!');
    }

    // Agora faz soft delete automaticamente (o Model usa o trait SoftDeletes).
    // O registro some das listagens normais, mas continua no banco e pode ser
    // restaurado na Lixeira.
    public function destroy($id)
    {
        Boleto::findOrFail($id)->delete();
        return redirect()->route('dashboard')->with('success', 'Boleto movido para a lixeira!');
    }

    public function pagarLote(Request $request)
    {
        $ids = $request->input('ids');
        if (!$ids || count($ids) === 0) {
            return redirect()->back()->with('error', 'Nenhum boleto selecionado.');
        }

        Boleto::whereIn('id', $ids)->where('status', 'pendente')->update([
            'status'         => 'pago',
            'data_pagamento' => now(),
        ]);

        return redirect()->route('dashboard')->with('success', count($ids) . ' boleto(s) pago(s) com sucesso!');
    }

    // ─── Lixeira (soft deletes) ─────────────────────────────────────────

    public function lixeira()
    {
        $boletos = Boleto::onlyTrashed()
            ->orderBy('deleted_at', 'desc')
            ->paginate(15);

        return view('boletos.lixeira', compact('boletos'));
    }

    public function restaurar($id)
    {
        $boleto = Boleto::onlyTrashed()->findOrFail($id);
        $boleto->restore();

        return redirect()->route('boletos.lixeira')->with('success', "Boleto de {$boleto->beneficiario} restaurado com sucesso!");
    }

    public function excluirDefinitivo($id)
    {
        $boleto = Boleto::onlyTrashed()->findOrFail($id);
        $nome   = $boleto->beneficiario;
        $boleto->forceDelete();

        return redirect()->route('boletos.lixeira')->with('success', "Boleto de {$nome} excluído permanentemente.");
    }

    public function visualizarBarcode($id)
    {
        $boleto  = Boleto::findOrFail($id);
        $linha   = preg_replace('/\D/', '', $boleto->codigo_barras);
        $tamanho = strlen($linha);
        $tipo    = '';
        $aviso   = null;

        if ($tamanho === 44) {
            $codigo44 = $linha;
            $tipo     = str_starts_with($linha, '8') ? 'convenio' : 'bancario';

        } elseif ($tamanho === 47 && !str_starts_with($linha, '8')) {
            $codigo44 = substr($linha, 0, 3)
                      . substr($linha, 3, 1)
                      . substr($linha, 32, 1)
                      . substr($linha, 33, 14)
                      . substr($linha, 4, 5)
                      . substr($linha, 10, 10)
                      . substr($linha, 21, 10);
            $tipo = 'bancario';

        } elseif ($tamanho === 48 && str_starts_with($linha, '8')) {
            $codigo44 = $this->convenio48paraCodigo44($linha);
            $tipo     = 'convenio';

        } elseif ($tamanho === 47 && str_starts_with($linha, '8')) {
            $codigo44 = substr($linha, 0, 11)
                      . substr($linha, 12, 11)
                      . substr($linha, 24, 11)
                      . substr($linha, 36, 11);
            $tipo = 'convenio';

        } else {
            $codigo44 = $linha;
            $tipo     = 'desconhecido';
            $aviso    = "Código com {$tamanho} dígitos não reconhecido.";
        }

        $generator  = new \Picqer\Barcode\BarcodeGeneratorSVG();
        $barcodeSvg = $generator->getBarcode(
            $codigo44,
            $generator::TYPE_INTERLEAVED_2_5,
            2,
            100
        );

        return view('boletos.show', [
            'barcode'  => $barcodeSvg,
            'numero'   => $boleto->codigo_barras,
            'boleto'   => $boleto,
            'tamanho'  => $tamanho,
            'tipo'     => $tipo,
            'aviso'    => $aviso,
            'codigo44' => $codigo44,
        ]);
    }

    public function buscarBeneficiarios(Request $request)
    {
        $termo = $request->query('q', '');

        $resultados = BeneficiarioIdentificado::where('nome_sugerido', 'like', '%' . $termo . '%')
            ->orderBy('nome_sugerido')
            ->limit(10)
            ->pluck('nome_sugerido')
            ->unique()
            ->values();

        return response()->json($resultados);
    }

    // ─── Relatórios ──────────────────────────────────────────────────────

    /**
     * Monta todos os dados usados pelo relatório (tela, PDF e CSV puxam
     * daqui, pra garantir que os três mostrem exatamente os mesmos números).
     */
    private function dadosRelatorio(Request $request): array
    {
        $dataInicio = $request->input('data_inicio', now()->startOfMonth()->format('Y-m-d'));
        $dataFim    = $request->input('data_fim', now()->endOfMonth()->format('Y-m-d'));
        $hoje       = now()->format('Y-m-d');

        $baseQuery = Boleto::where('user_id', Auth::id())
            ->whereBetween('data_vencimento', [$dataInicio, $dataFim]);

        $totalPago     = (clone $baseQuery)->where('status', 'pago')->sum('valor');
        $qtdPago       = (clone $baseQuery)->where('status', 'pago')->count();

        $totalPendente = (clone $baseQuery)->where('status', 'pendente')->where('data_vencimento', '>=', $hoje)->sum('valor');
        $qtdPendente   = (clone $baseQuery)->where('status', 'pendente')->where('data_vencimento', '>=', $hoje)->count();

        $totalVencido  = (clone $baseQuery)->where('status', 'pendente')->where('data_vencimento', '<', $hoje)->sum('valor');
        $qtdVencido    = (clone $baseQuery)->where('status', 'pendente')->where('data_vencimento', '<', $hoje)->count();

        $totalGeral = $totalPago + $totalPendente + $totalVencido;

        $mesesPt = ['01'=>'Jan','02'=>'Fev','03'=>'Mar','04'=>'Abr','05'=>'Mai','06'=>'Jun',
                    '07'=>'Jul','08'=>'Ago','09'=>'Set','10'=>'Out','11'=>'Nov','12'=>'Dez'];

        $todosDoPeriodo = (clone $baseQuery)->with('categoria')->get();

        $porMes = $todosDoPeriodo
            ->groupBy(fn($item) => Carbon::parse($item->data_vencimento)->format('Y-m'))
            ->map(function ($grupo, $mes) use ($mesesPt) {
                return [
                    'mes'   => $mes,
                    'label' => $mesesPt[substr($mes, 5, 2)] . '/' . substr($mes, 0, 4),
                    'total' => (float) $grupo->sum('valor'),
                    'qtd'   => $grupo->count(),
                ];
            })
            ->sortKeys()
            ->values();

        // Comparativo mês a mês: calcula a variação percentual de cada mês
        // em relação ao mês imediatamente anterior dentro do período filtrado.
        // O primeiro mês do período não tem "anterior" pra comparar, então fica null.
        $porMesArray = $porMes->toArray();
        foreach ($porMesArray as $i => &$item) {
            if ($i === 0) {
                $item['variacao'] = null;
            } else {
                $anterior = $porMesArray[$i - 1]['total'];
                $item['variacao'] = $anterior > 0
                    ? round((($item['total'] - $anterior) / $anterior) * 100, 1)
                    : null;
            }
        }
        unset($item);
        $porMes = collect($porMesArray);

        // Agrupamento por categoria (para o gráfico de pizza de categorias).
        // Boletos sem categoria vinculada entram como "Sem categoria".
        $porCategoria = $todosDoPeriodo
            ->groupBy(fn($item) => $item->categoria_id ?? 0)
            ->map(function ($grupo) {
                $primeiro = $grupo->first();
                return [
                    'categoria_id' => $primeiro->categoria_id,
                    'label'        => $primeiro->categoria?->nome ?? 'Sem categoria',
                    'total'        => (float) $grupo->sum('valor'),
                    'qtd'          => $grupo->count(),
                ];
            })
            ->sortByDesc('total')
            ->values();

        $boletos = (clone $baseQuery)->with('categoria')->orderBy('data_vencimento')->get();

        // Comparativo com o período anterior de mesma duração
        // (ex: se o filtro é 01/08 a 31/08 [31 dias], compara com 01/07 a 31/07)
        $inicio      = Carbon::parse($dataInicio);
        $fim         = Carbon::parse($dataFim);
        $diasPeriodo = $inicio->diffInDays($fim) + 1;

        $dataFimAnterior    = $inicio->copy()->subDay()->format('Y-m-d');
        $dataInicioAnterior = $inicio->copy()->subDays($diasPeriodo)->format('Y-m-d');

        $totalPeriodoAnterior = Boleto::where('user_id', Auth::id())
            ->whereBetween('data_vencimento', [$dataInicioAnterior, $dataFimAnterior])
            ->sum('valor');

        $variacaoPeriodo = $totalPeriodoAnterior > 0
            ? round((($totalGeral - $totalPeriodoAnterior) / $totalPeriodoAnterior) * 100, 1)
            : null;

        return compact(
            'dataInicio', 'dataFim',
            'totalPago', 'qtdPago',
            'totalPendente', 'qtdPendente',
            'totalVencido', 'qtdVencido',
            'totalGeral', 'porMes', 'porCategoria', 'boletos', 'hoje',
            'totalPeriodoAnterior', 'variacaoPeriodo',
            'dataInicioAnterior', 'dataFimAnterior'
        );
    }

    public function relatorios(Request $request)
    {
        return view('boletos.relatorio', $this->dadosRelatorio($request));
    }

    public function exportarPdf(Request $request)
    {
        $dados = $this->dadosRelatorio($request);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('boletos.relatorio-pdf', $dados)
            ->setPaper('a4', 'portrait');

        $nomeArquivo = "relatorio-boletos-{$dados['dataInicio']}-a-{$dados['dataFim']}.pdf";

        return $pdf->download($nomeArquivo);
    }

    public function exportarCsv(Request $request)
    {
        $dados   = $this->dadosRelatorio($request);
        $boletos = $dados['boletos'];
        $hoje    = $dados['hoje'];

        $nomeArquivo = "relatorio-boletos-{$dados['dataInicio']}-a-{$dados['dataFim']}.csv";

        $callback = function () use ($boletos, $hoje) {
            $handle = fopen('php://output', 'w');

            // BOM UTF-8, para o Excel reconhecer acentuação corretamente
            fwrite($handle, "\xEF\xBB\xBF");

            fputcsv($handle, ['Beneficiário', 'Categoria', 'Vencimento', 'Pagamento', 'Valor', 'Status'], ';');

            foreach ($boletos as $boleto) {
                $ehVencido = $boleto->status === 'pendente'
                    && $boleto->data_vencimento->format('Y-m-d') < $hoje;

                $statusLabel = $boleto->status === 'pago'
                    ? 'Pago'
                    : ($ehVencido ? 'Vencido' : 'Pendente');

                fputcsv($handle, [
                    $boleto->beneficiario,
                    $boleto->categoria_label,
                    $boleto->data_vencimento->format('d/m/Y'),
                    $boleto->data_pagamento ? $boleto->data_pagamento->format('d/m/Y') : '',
                    number_format($boleto->valor, 2, ',', '.'),
                    $statusLabel,
                ], ';');
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$nomeArquivo}\"",
        ]);
    }
}
