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
    // Validação
    $request->validate([
        'beneficiario' => 'required|string|max:255',
        'valor' => 'required',
        'data_vencimento' => 'required|date',
    ]);

    $isParcelado = $request->has('repetir');
    $totalParcelas = $isParcelado ? (int)$request->input('parcelas', 1) : 1;
    
    $dataInicial = Carbon::parse($request->data_vencimento);

    for ($i = 0; $i < $totalParcelas; $i++) {
        $vencimento = $dataInicial->copy()->addMonths($i);

        $nomeFinal = $totalParcelas > 1
            ? $request->beneficiario . " (" . str_pad($i + 1, 2, '0', STR_PAD_LEFT) . "/" . str_pad($totalParcelas, 2, '0', STR_PAD_LEFT) . ")"
            : $request->beneficiario;

        Boleto::create([
            'beneficiario'    => $nomeFinal,
            'valor'           => $request->valor,
            'data_vencimento' => $vencimento,
            'codigo_barras'   => $request->codigo_barras,
            'status'          => 'pendente',
            'user_id'         => auth()->id(),
        ]);
    }

    $mensagem = $totalParcelas > 1
        ? "$totalParcelas boletos gerados com sucesso para os próximos meses!"
        : "Boleto cadastrado com sucesso!";

    return back()->with('success', $mensagem);
}

    public function index(Request $request) {
        $query = Boleto::query();

        if ($request->filled('beneficiario')) {
            // Alterado de 'ilike' para 'like' para evitar erros em MySQL/SQLite
            $query->where('beneficiario', 'like', '%' . $request->beneficiario . '%');
        }

        if ($request->filled('valor')) {
            $query->where('valor', $request->valor);
        }

        if ($request->filled('data_vencimento')) {
            $query->where('data_vencimento', $request->data_vencimento);
        }

        $boletos = $query->orderBy('data_vencimento', 'asc')->get();
        $hoje = date('Y-m-d');

        // Cálculos
        $totalDia = Boleto::where('status', 'pendente')
            ->whereDate('data_vencimento', $hoje)
            ->sum('valor');

        $totalSemana = Boleto::where('status', 'pendente')
            ->whereBetween('data_vencimento', [now()->startOfWeek(), now()->endOfWeek()])
            ->sum('valor');

        $totalMes = Boleto::where('status', 'pendente')
            ->whereMonth('data_vencimento', now()->month)
            ->whereYear('data_vencimento', now()->year)
            ->sum('valor');

        return view('boletos.index', compact('boletos', 'hoje', 'totalDia', 'totalSemana', 'totalMes'));
    }

    public function pagar($id) {
        $boleto = Boleto::findOrFail($id);
        $boleto->update([
            'status' => 'pago',
            'data_pagamento' => date('Y-m-d')
        ]);

        // Corrigido: Agora redireciona para 'dashboard'
        return redirect()->route('dashboard')->with('success', 'Boleto pago!');
    }

    public function edit($id) {
        $boleto = Boleto::findOrFail($id);
        return view('boletos.edit', compact('boleto'));
    }

    public function update(Request $request, $id) {
        $boleto = Boleto::findOrFail($id);
        
        if ($request->status == 'pago' && $boleto->status == 'pendente') {
            $request->merge(['data_pagamento' => date('Y-m-d')]);
        } elseif ($request->status == 'pendente') {
            $request->merge(['data_pagamento' => null]);
        }

        $valor = str_replace(',', '.', str_replace('.', '', $request->valor));
        $boleto->update([
        'beneficiario' => $request->beneficiario,
        'valor' => $valor,
        'data_vencimento' => $request->data_vencimento,
        'codigo_barras' => $request->codigo_barras,
        'status' => $request->status,
    ]);

    return redirect()->route('dashboard')->with('success', 'Boleto atualizado com sucesso!');
}

    public function destroy($id) {
        $boleto = Boleto::findOrFail($id);
        $boleto->delete();
        // Corrigido: Agora redireciona para 'dashboard'
        return redirect()->route('dashboard')->with('success', 'Boleto excluído!');
    }
}