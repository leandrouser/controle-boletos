<?php

use App\Http\Controllers\BoletoController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// Redireciona a raiz para o login
Route::get('/', function () {
    return redirect()->route('login');
});

// Todas as rotas protegidas por senha
Route::middleware(['auth', 'verified'])->group(function () {
    
    // ESSA É A ROTA PRINCIPAL (Onde o Breeze vai te jogar após o login)
    // Substituímos o nome '' por 'dashboard'
    Route::get('/dashboard', [BoletoController::class, 'index'])->name('dashboard');

    // Rotas de Gestão de Boletos
    Route::get('/boletos/novo', [BoletoController::class, 'create'])->name('boletos.create');
    Route::post('/boletos', [BoletoController::class, 'store'])->name('boletos.store');
    Route::get('/boletos/{id}/editar', [BoletoController::class, 'edit'])->name('boletos.edit');
    Route::put('/boletos/{id}', [BoletoController::class, 'update'])->name('boletos.update');
    Route::delete('/boletos/{id}', [BoletoController::class, 'destroy'])->name('boletos.destroy');
    Route::post('/boletos/{id}/pagar', [BoletoController::class, 'pagar'])->name('boletos.pagar');
    
    // Rota do PDF
    Route::get('/boletos/relatorio', [BoletoController::class, 'gerarPdf'])->name('boletos.pdf');

    // Rotas de Perfil (Opcional, mas evita erros se você clicar no menu)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';