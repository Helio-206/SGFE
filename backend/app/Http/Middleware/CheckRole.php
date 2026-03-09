<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de verificação de role (RBAC).
 *
 * Uso na rota: ->middleware('role:admin,gestor')
 */
class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles)) {
            abort(403, 'Acesso negado. Papel insuficiente.');
        }

        return $next($request);
    }
}
