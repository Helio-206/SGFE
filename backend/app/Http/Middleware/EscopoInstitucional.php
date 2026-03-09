<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware de Escopo Institucional
 *
 * Garante que um utilizador autenticado só aceda a dados
 * da sua própria instituição (id_inst).
 * Admins são isentos deste filtro — gerem todas as instituições.
 */
class EscopoInstitucional
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Admins podem aceder a dados de qualquer instituição
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Verifica se a rota tem parâmetro id_inst e se corresponde ao do utilizador
        $idInstRota = $request->route('id_inst') ?? $request->input('id_inst');

        if ($idInstRota && (int) $idInstRota !== (int) $user->id_inst) {
            abort(403, 'Não tem permissão para aceder a dados de outra instituição.');
        }

        return $next($request);
    }
}
