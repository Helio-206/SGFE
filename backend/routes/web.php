<?php

use App\Http\Controllers\Admin\ClassificacaoEconomicaController;
use App\Http\Controllers\Admin\InstituicaoController;
use App\Http\Controllers\Admin\OrcamentoController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Gestao\OrcamentoConsultaController;
use App\Http\Controllers\Gestao\TransacaoDespesaController;
use App\Http\Controllers\Gestao\TransacaoReceitaController;
use App\Http\Controllers\RelatorioFinanceiroController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ── Rotas protegidas por RBAC + Escopo Institucional ──────────

// ADMIN — Gestão de Instituições, Orçamentos e Classificações
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    // Instituições (UOs)
    Route::resource('instituicoes', InstituicaoController::class)
        ->parameters(['instituicoes' => 'instituicao']);
    
    // Orçamentos / Tectos
    Route::resource('orcamentos', OrcamentoController::class);
    
    // Classificações Económicas
    Route::resource('classificacoes', ClassificacaoEconomicaController::class)
        ->except(['show']);
});

// GESTOR — Receitas e Despesas (filtradas por id_inst)
Route::middleware(['auth', 'role:admin,gestor', 'escopo.institucional'])->prefix('gestao')->name('gestao.')->group(function () {
    Route::get('/tecto', [OrcamentoConsultaController::class, 'index'])->name('tecto.index');

    Route::get('/receitas', [TransacaoReceitaController::class, 'index'])->name('receitas.index');
    Route::get('/receitas/create', [TransacaoReceitaController::class, 'create'])->name('receitas.create');
    Route::post('/receitas', [TransacaoReceitaController::class, 'store'])->name('receitas.store');

    Route::get('/despesas', [TransacaoDespesaController::class, 'index'])->name('despesas.index');
    Route::get('/despesas/create', [TransacaoDespesaController::class, 'create'])->name('despesas.create');
    Route::post('/despesas', [TransacaoDespesaController::class, 'store'])->name('despesas.store');
    Route::patch('/despesas/{despesa}/liquidar', [TransacaoDespesaController::class, 'liquidar'])->name('despesas.liquidar');
    Route::patch('/despesas/{despesa}/pagar', [TransacaoDespesaController::class, 'pagar'])->name('despesas.pagar');
});

// AUDITOR + GESTOR + ADMIN — Relatórios (read-only)
Route::middleware(['auth', 'role:admin,gestor,auditor'])->prefix('relatorios')->name('relatorios.')->group(function () {
    Route::get('/', [RelatorioFinanceiroController::class, 'index'])->name('index');
    Route::get('/consolidado-gastos', [RelatorioFinanceiroController::class, 'consolidadoGastos'])->name('consolidado.gastos');
    Route::get('/evolucao-receitas-mensal', [RelatorioFinanceiroController::class, 'evolucaoReceitasMensal'])->name('evolucao.receitas');
    Route::get('/exportar-pdf', [RelatorioFinanceiroController::class, 'exportarPdf'])->name('exportar.pdf');
    Route::get('/despesa-por-natureza', [RelatorioFinanceiroController::class, 'despesaPorNatureza'])->name('despesa.natureza');
    Route::get('/exportar-receitas-excel', [RelatorioFinanceiroController::class, 'exportarReceitasExcel'])->name('exportar.receitas.excel');
});

require __DIR__.'/auth.php';
