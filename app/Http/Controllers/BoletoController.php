<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Boleto;
use Carbon\Carbon;

class BoletoController extends Controller
{
    public function create() {
        return view('boletos.create');
    }

    public function store(Request $request)
{
    $request->validate([
        'beneficiario' => 'required|string|max:255',
        'valor' => 'required',
        'data_vencimento' => 'required|date',
    ]);

    $isParcelado = $request->has('repetir') && $request->has('vencimentos_parcelas');
    
    if ($isParcelado) {
        $vencimentos = $request->input('vencimentos_parcelas');
        $valores = $request->input('valores_parcelas');
        $total = count($vencimentos);

        foreach ($vencimentos as $index => $data) {
            $valorLimpo = str_replace(['.', ','], ['', '.'], $valores[$index]);

            Boleto::create([
                'beneficiario'    => $request->beneficiario . " (" . ($index + 1) . "/{$total})",
                'valor'           => $valorLimpo,
                'data_vencimento' => $data,
                'codigo_barras'   => $request->codigo_barras,
                'status'          => 'pendente',
                'user_id'         => auth()->id(),
            ]);
        }
    } else {
        $valorLimpo = str_replace(['.', ','], ['', '.'], $request->valor);
        Boleto::create([
            'beneficiario'    => $request->beneficiario,
            'valor'           => $valorLimpo,
            'data_vencimento' => $request->data_vencimento,
            'codigo_barras'   => $request->codigo_barras,
            'status'          => 'pendente',
            'user_id'         => auth()->id(),
        ]);
    }

    return redirect()->route('dashboard')->with('success', 'Lançamento(s) realizado(s) com sucesso!');
}

    public function index(Request $request) {
    $query = Boleto::query();

    if ($request->filled('beneficiario')) {
        $query->where('beneficiario', 'like', '%' . $request->beneficiario . '%');
    }
    if ($request->filled('valor')) {
        $query->where('valor', $request->valor);
    }
    if ($request->filled('data_vencimento')) {
        $query->where('data_vencimento', $request->data_vencimento);
    }

    $boletos = $query->orderBy('data_vencimento', 'asc')->paginate(10)->withQueryString();
    
    $hoje = \Carbon\Carbon::today();
    $fimSemana = \Carbon\Carbon::now()->endOfWeek();
    $fimMes = \Carbon\Carbon::now()->endOfMonth();

    $baseHoje = Boleto::where('status', 'pendente')->whereDate('data_vencimento', '<=', $hoje);
    $totalDia = (float) $baseHoje->sum('valor');
    $qtdDia   = $baseHoje->count();

    $baseSemana = Boleto::where('status', 'pendente')->whereDate('data_vencimento', '<=', $fimSemana);
    $totalSemana = (float) $baseSemana->sum('valor');
    $qtdSemana   = $baseSemana->count();

    $baseMes = Boleto::where('status', 'pendente')->whereDate('data_vencimento', '<=', $fimMes);
    $totalMes = (float) $baseMes->sum('valor');
    $qtdMes   = $baseMes->count();

    return view('boletos.index', compact(
        'boletos', 'hoje',
        'totalDia', 'totalSemana', 'totalMes',
        'qtdDia', 'qtdSemana', 'qtdMes'
    ));
}

    public function pagar($id) {
        $boleto = Boleto::findOrFail($id);
        $boleto->update([
            'status' => 'pago',
            'data_pagamento' => date('Y-m-d')
        ]);

        return redirect()->route('dashboard')->with('success', 'Boleto pago!');
    }

    public function edit($id) {
        $boleto = Boleto::findOrFail($id);
        return view('boletos.edit', compact('boleto'));
    }

    public function update(Request $request, $id) {
        $boleto = Boleto::findOrFail($id);
        $request->validate([
        'beneficiario'    => 'required|string|max:255',
        'valor'           => 'required',
        'data_vencimento' => 'required|date',
    ]);
        if ($request->status == 'pago' && $boleto->status == 'pendente') {
            $dataPagamento = now();
    } elseif ($request->status == 'pendente') {
        $dataPagamento = null;
    }

        $boleto->update([
        'beneficiario'    => $request->beneficiario,
        'valor'           => $request->valor,
        'data_vencimento' => $request->data_vencimento,
        'codigo_barras'   => $request->codigo_barras,
        'status'          => $request->status,
        'data_pagamento'  => $dataPagamento,
    ]);

    return redirect()->route('dashboard')->with('success', 'Boleto atualizado com sucesso!');
}

    public function destroy($id) {
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
            'status' => 'pago',
            'data_pagamento' => now()
        ]);

    return redirect()->route('dashboard')->with('success', count($ids) . ' boletos foram pagos com sucesso!');
}
}