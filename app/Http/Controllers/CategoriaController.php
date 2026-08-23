<?php

namespace App\Http\Controllers;

use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    public function index()
    {
        $categorias = Categoria::withCount('boletos')->orderBy('nome')->get();
        return view('categorias.index', compact('categorias'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nome' => 'required|string|max:255|unique:categorias,nome',
        ], [
            'nome.unique' => 'Já existe uma categoria com esse nome.',
        ]);

        Categoria::create(['nome' => $request->nome]);

        return redirect()->route('categorias.index')->with('success', 'Categoria criada com sucesso!');
    }

    public function update(Request $request, Categoria $categoria)
    {
        $request->validate([
            'nome' => 'required|string|max:255|unique:categorias,nome,' . $categoria->id,
        ], [
            'nome.unique' => 'Já existe uma categoria com esse nome.',
        ]);

        $categoria->update(['nome' => $request->nome]);

        return redirect()->route('categorias.index')->with('success', 'Categoria atualizada com sucesso!');
    }

    public function destroy(Categoria $categoria)
    {
        $qtdBoletos = $categoria->boletos()->count();

        if ($qtdBoletos > 0) {
            return redirect()->route('categorias.index')
                ->with('error', "Não é possível excluir '{$categoria->nome}': há {$qtdBoletos} boleto(s) usando essa categoria. Edite esses boletos antes de excluir.");
        }

        $categoria->delete();

        return redirect()->route('categorias.index')->with('success', 'Categoria excluída com sucesso!');
    }
}
