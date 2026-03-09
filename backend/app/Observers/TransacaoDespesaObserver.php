<?php

namespace App\Observers;

use App\Models\TransacaoDespesa;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TransacaoDespesaObserver
{
    /**
     * RNF08 - Audit log quando estado muda para PAGA.
     */
    public function updated(TransacaoDespesa $transacaoDespesa): void
    {
        if (! $transacaoDespesa->wasChanged('estado')) {
            return;
        }

        if ($transacaoDespesa->estado !== TransacaoDespesa::ESTADO_PAGA) {
            return;
        }

        $usuario = Auth::user();

        Log::info('SGFE Audit - Despesa paga', [
            'evento' => 'despesa_paga',
            'id_despesa' => $transacaoDespesa->id_despesa,
            'id_inst' => $transacaoDespesa->id_inst,
            'valor_bruto' => $transacaoDespesa->valor_bruto,
            'estado_anterior' => $transacaoDespesa->getOriginal('estado'),
            'estado_novo' => $transacaoDespesa->estado,
            'autorizado_por_id_user' => $usuario?->id_user,
            'autorizado_por_email' => $usuario?->email,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
