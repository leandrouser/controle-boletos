<?php

namespace App\Http\Controllers;

use App\Models\BeneficiarioIdentificado;
use App\Models\Boleto;
use Carbon\Carbon;
use Illuminate\Http\Request;

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
        $categorias = Boleto::CATEGORIAS;
        return view('boletos.create', compact('categorias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'beneficiario'    => 'required|string|max:255',
            'categoria'       => 'nullable|string|in:' . implode(',', array_keys(Boleto::CATEGORIAS)),
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

        $userId           = \Illuminate\Support\Facades\Auth::id();
        $contaOrigem      = $request->input('conta_origem');
        $assinatura       = $request->input('assinatura_origem');
        $nomeBeneficiario = $request->input('beneficiario');
        $categoria        = $request->input('categoria');

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
                    'categoria'         => $categoria,
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
                'categoria'         => $categoria,
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

        if ($request->filled('categoria')) {
            $query->where('categoria', $request->categoria);
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

        $categorias = Boleto::CATEGORIAS;

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
        $categorias = Boleto::CATEGORIAS;
        return view('boletos.edit', compact('boleto', 'categorias'));
    }

    public function update(Request $request, $id)
    {
        $boleto = Boleto::findOrFail($id);

        $request->validate([
            'beneficiario'    => 'required|string|max:255',
            'categoria'       => 'nullable|string|in:' . implode(',', array_keys(Boleto::CATEGORIAS)),
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
            'categoria'       => $request->categoria,
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

    public function destroy($id)
    {
        Boleto::findOrFail($id)->delete();
        return redirect()->route('dashboard')->with('success', 'Boleto excluído!');
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

    public function relatorios(Request $request)
    {
        $dataInicio = $request->input('data_inicio', now()->startOfMonth()->format('Y-m-d'));
        $dataFim    = $request->input('data_fim', now()->endOfMonth()->format('Y-m-d'));
        $hoje       = now()->format('Y-m-d');

        $baseQuery = Boleto::where('user_id', \Illuminate\Support\Facades\Auth::id())
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

        $todosDoPeriodo = (clone $baseQuery)->get();

        $porMes = $todosDoPeriodo
            ->groupBy(fn($item) => \Carbon\Carbon::parse($item->data_vencimento)->format('Y-m'))
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

        // Agrupamento por categoria (para o gráfico de pizza de categorias).
        // Boletos sem categoria definida entram como "Outros".
        $porCategoria = $todosDoPeriodo
            ->groupBy(fn($item) => $item->categoria ?? 'outros')
            ->map(function ($grupo, $categoria) {
                return [
                    'categoria' => $categoria,
                    'label'     => Boleto::CATEGORIAS[$categoria] ?? 'Outros',
                    'total'     => (float) $grupo->sum('valor'),
                    'qtd'       => $grupo->count(),
                ];
            })
            ->sortByDesc('total')
            ->values();

        $boletos = (clone $baseQuery)->orderBy('data_vencimento')->get();

        return view('boletos.relatorio', compact(
            'dataInicio', 'dataFim',
            'totalPago', 'qtdPago',
            'totalPendente', 'qtdPendente',
            'totalVencido', 'qtdVencido',
            'totalGeral', 'porMes', 'porCategoria', 'boletos', 'hoje'
        ));
    }
}
