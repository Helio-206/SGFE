<?php

namespace App\Providers;

use App\Models\TransacaoDespesa;
use App\Models\User;
use App\Observers\TransacaoDespesaObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * Configura Gates de RBAC conforme RF01:
     *  - ADMIN: Gerencia instituições e tectos orçamentais globais
     *  - GESTOR: Registra receitas e despesas da sua instituição
     *  - AUDITOR: Acesso de leitura (read-only)
     */
    public function boot(): void
    {
        TransacaoDespesa::observe(TransacaoDespesaObserver::class);

        // ── Gates de nível de acesso ────────────────────────

        // Admin pode tudo
        Gate::before(function (User $user, string $ability) {
            if ($user->isAdmin()) {
                return true;
            }
        });

        // Gerir instituições — apenas Admin
        Gate::define('gerir-instituicoes', function (User $user) {
            return $user->isAdmin();
        });

        // Gerir tectos orçamentais — apenas Admin
        Gate::define('gerir-orcamentos', function (User $user) {
            return $user->isAdmin();
        });

        // Registrar receitas — Admin ou Gestor
        Gate::define('registrar-receitas', function (User $user) {
            return $user->isGestor();
        });

        // Registrar despesas — Admin ou Gestor
        Gate::define('registrar-despesas', function (User $user) {
            return $user->isGestor();
        });

        // Ver relatórios — todos os papéis (incl. Auditor)
        Gate::define('ver-relatorios', function (User $user) {
            return in_array($user->role, ['admin', 'gestor', 'auditor']);
        });
    }
}
