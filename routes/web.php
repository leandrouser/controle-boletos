<?php

use App\Http\Controllers\BoletoController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/dashboard', [BoletoController::class, 'index'])->name('dashboard');

    Route::get('/boletos/novo', [BoletoController::class, 'create'])->name('boletos.create');
    Route::post('/boletos/pagar-lote', [BoletoController::class, 'pagarLote'])->name('boletos.pagarLote');
    Route::get('/boletos/relatorio', [BoletoController::class, 'gerarPdf'])->name('boletos.pdf');
    Route::get('/boletos/beneficiarios/buscar', [BoletoController::class, 'buscarBeneficiarios'])->name('boletos.beneficiarios.buscar');

    Route::get('/boletos/relatorios', [BoletoController::class, 'relatorios'])->name('boletos.relatorios');
    Route::get('/boletos/relatorios/pdf', [BoletoController::class, 'exportarPdf'])->name('boletos.relatorios.pdf');
    Route::get('/boletos/relatorios/csv', [BoletoController::class, 'exportarCsv'])->name('boletos.relatorios.csv');

    Route::get('/boletos/lixeira', [BoletoController::class, 'lixeira'])->name('boletos.lixeira');
    Route::post('/boletos/{id}/restaurar', [BoletoController::class, 'restaurar'])->name('boletos.restaurar');
    Route::delete('/boletos/{id}/forcar', [BoletoController::class, 'excluirDefinitivo'])->name('boletos.excluirDefinitivo');

    Route::get('/categorias', [\App\Http\Controllers\CategoriaController::class, 'index'])->name('categorias.index');
    Route::post('/categorias', [\App\Http\Controllers\CategoriaController::class, 'store'])->name('categorias.store');
    Route::put('/categorias/{categoria}', [\App\Http\Controllers\CategoriaController::class, 'update'])->name('categorias.update');
    Route::delete('/categorias/{categoria}', [\App\Http\Controllers\CategoriaController::class, 'destroy'])->name('categorias.destroy');


    Route::post('/boletos', [BoletoController::class, 'store'])->name('boletos.store');

    Route::get('/boletos/{id}/editar', [BoletoController::class, 'edit'])->name('boletos.edit');
    Route::put('/boletos/{id}', [BoletoController::class, 'update'])->name('boletos.update');
    Route::delete('/boletos/{id}', [BoletoController::class, 'destroy'])->name('boletos.destroy');
    Route::post('/boletos/{id}/pagar', [BoletoController::class, 'pagar'])->name('boletos.pagar');
    Route::get('/boletos/{id}/barcode', [BoletoController::class, 'visualizarBarcode'])->name('boletos.show');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/api/consultar-beneficiario/{assinatura}', [BoletoController::class, 'consultarAssinatura']);
    Route::get('/api/verificar-boleto-duplicado', [BoletoController::class, 'verificarDuplicado']);
    Route::get('/api/consultar-conta/{conta}', [BoletoController::class, 'consultarConta']);
});

require __DIR__.'/auth.php';
