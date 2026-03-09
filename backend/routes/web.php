<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ── Rotas protegidas por RBAC + Escopo Institucional ──────────

// ADMIN — Gestão de Instituições e Orçamentos globais
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/instituicoes', function () {
        return 'Gestão de Instituições (Admin)';
    })->name('instituicoes.index');

    Route::get('/orcamentos', function () {
        return 'Tectos Orçamentais Globais (Admin)';
    })->name('orcamentos.index');
});

// GESTOR — Receitas e Despesas (filtradas por id_inst)
Route::middleware(['auth', 'role:admin,gestor', 'escopo.institucional'])->prefix('gestao')->name('gestao.')->group(function () {
    Route::get('/receitas', function () {
        $user = auth()->user();
        $receitas = \App\Models\TransacaoReceita::where('id_inst', $user->id_inst)->get();
        return response()->json($receitas);
    })->name('receitas.index');

    Route::get('/despesas', function () {
        $user = auth()->user();
        $despesas = \App\Models\TransacaoDespesa::where('id_inst', $user->id_inst)->get();
        return response()->json($despesas);
    })->name('despesas.index');
});

// AUDITOR + GESTOR + ADMIN — Relatórios (read-only)
Route::middleware(['auth', 'role:admin,gestor,auditor'])->prefix('relatorios')->name('relatorios.')->group(function () {
    Route::get('/', function () {
        return 'Relatórios e Fiscalização';
    })->name('index');
});

require __DIR__.'/auth.php';
