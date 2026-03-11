<?php

namespace App\Http\Controllers;

use App\Models\BeneficiarioIdentificado;
use Illuminate\Http\Request;
use App\Models\Boleto;
use Carbon\Carbon;

class BoletoController extends Controller
{
    /**
     * Converte valor do formato brasileiro (1.234,56) para float.
     * Também aceita formato inglês (1234.56) como fallback seguro.
     */
    private function parseBrValue(string $valor): float
    {
        $v = trim($valor);

        // Formato BR: tem vírgula como decimal → ex: 1.234,56 ou 234,56
        if (str_contains($v, ',')) {
            $v = str_replace('.', '', $v);  // remove separador de milhar
            $v = str_replace(',', '.', $v); // vírgula → ponto decimal
        }
        // Formato EN puro (ex: 1234.56) — não faz nada, já está correto

        return (float) $v;
    }

    public function create()
    {
        return view('boletos.create');
    }

    public function store(Request $request)
    {
        if ($request->filled('codigo_barras')) {
            $codigoLimpo = preg_replace('/[^0-9]/', '', $request->codigo_barras);

            $existe = Boleto::where('codigo_barras', $codigoLimpo)->first();

            if ($existe) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "Erro: Este boleto já foi cadastrado anteriormente para {$existe->beneficiario}.");
            }
        }

        $request->validate([
            'beneficiario'    => 'required|string|max:255',
            'valor'           => 'required',
            'data_vencimento' => 'required|date',
        ]);

        $userId    = \Illuminate\Support\Facades\Auth::id();
        $assinatura = $request->input('assinatura_origem');

        $isParcelado = $request->has('repetir') && $request->has('vencimentos_parcelas');

        if ($isParcelado) {
            $vencimentos = $request->input('vencimentos_parcelas');
            $valores     = $request->input('valores_parcelas');
            $total       = count($vencimentos);

            foreach ($vencimentos as $index => $data) {
                Boleto::create([
                    'beneficiario'      => $request->beneficiario . " (" . ($index + 1) . "/{$total})",
                    'valor'             => $this->parseBrValue($valores[$index]),
                    'data_vencimento'   => $data,
                    'codigo_barras'     => $request->codigo_barras,
                    'assinatura_origem' => $assinatura,
                    'status'            => 'pendente',
                    'user_id'           => $userId,
                ]);
            }
        } else {
            Boleto::create([
                'beneficiario'      => $request->beneficiario,
                'valor'             => $this->parseBrValue($request->valor),
                'data_vencimento'   => $request->data_vencimento,
                'codigo_barras'     => $request->codigo_barras,
                'assinatura_origem' => $assinatura,
                'status'            => 'pendente',
                'user_id'           => $userId,
            ]);
        }

        if (!empty($assinatura)) {
            \App\Models\BeneficiarioIdentificado::updateOrInsert(
                ['assinatura' => $assinatura],
                [
                    'nome_sugerido' => $request->beneficiario,
                    'updated_at'    => now(),
                ]
            );
        }

        return redirect()->route('dashboard')->with('success', 'Lançamento(s) realizado(s) com sucesso!');
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

    public function index(Request $request)
    {
        $query = Boleto::query();

        $status = $request->get('status', 'pendente');
        $query->where('status', $status);

        if ($request->filled('beneficiario')) {
            $query->where('beneficiario', 'like', '%' . $request->beneficiario . '%');
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

        return view('boletos.index', compact(
            'boletos', 'hoje', 'status',
            'totalDia', 'totalSemana', 'totalMes',
            'qtdDia', 'qtdSemana', 'qtdMes'
        ));
    }

    public function consultarAssinatura($assinatura)
    {
        $identificado = BeneficiarioIdentificado::where('assinatura', $assinatura)->first();

        return response()->json([
            'sucesso' => !!$identificado,
            'nome'    => $identificado ? $identificado->nome_sugerido : null,
        ]);
    }

    public function edit($id)
    {
        $boleto = Boleto::findOrFail($id);
        return view('boletos.edit', compact('boleto'));
    }

    public function update(Request $request, $id)
    {
        $boleto = Boleto::findOrFail($id);

        $request->validate([
            'beneficiario'    => 'required|string|max:255',
            'valor'           => 'required',
            'data_vencimento' => 'required|date',
        ]);

        $valorLimpo    = $this->parseBrValue($request->valor);
        $dataPagamento = $boleto->data_pagamento;

        if ($request->status == 'pago' && $boleto->status == 'pendente') {
            $dataPagamento = now();
        } elseif ($request->status == 'pendente') {
            $dataPagamento = null;
        }

        $boleto->update([
            'beneficiario'   => $request->beneficiario,
            'valor'          => $valorLimpo,
            'data_vencimento'=> $request->data_vencimento,
            'codigo_barras'  => $request->codigo_barras,
            'status'         => $request->status,
            'data_pagamento' => $dataPagamento,
        ]);

        return redirect()->route('dashboard')->with('success', 'Boleto atualizado com sucesso!');
    }

    public function pagar($id)
    {
        $boleto = Boleto::findOrFail($id);

        $boleto->update([
            'status'         => 'pago',
            'data_pagamento' => now(),
        ]);

        return redirect()->back()->with('success', 'Pagamento confirmado com sucesso!');
    }

    public function destroy($id)
    {
        $boleto = Boleto::findOrFail($id);
        $boleto->delete();

        return redirect()->route('dashboard')->with('success', 'Boleto excluído!');
    }

    public function pagarLote(Request $request)
    {
        $ids = $request->input('ids');

        if (!$ids || count($ids) === 0) {
            return redirect()->back()->with('error', 'Nenhum boleto selecionado.');
        }

        Boleto::whereIn('id', $ids)
            ->where('status', 'pendente')
            ->update([
                'status'         => 'pago',
                'data_pagamento' => now(),
            ]);

        return redirect()->route('dashboard')->with('success', count($ids) . ' boleto(s) pago(s) com sucesso!');
    }
}
