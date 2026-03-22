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
