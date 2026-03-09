<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Enforce enum de estados da despesa conforme fluxo oficial.
     */
    public function up(): void
    {
        DB::table('transacoes_despesas')->where('estado', 'cabimentada')->update(['estado' => 'PENDENTE_CABIMENTADA']);
        DB::table('transacoes_despesas')->where('estado', 'aprovado')->update(['estado' => 'LIQUIDADA_APROVADA']);
        DB::table('transacoes_despesas')->where('estado', 'pago')->update(['estado' => 'PAGA']);
        DB::table('transacoes_despesas')->where('estado', 'aprovada')->update(['estado' => 'LIQUIDADA_APROVADA']);
        DB::table('transacoes_despesas')->where('estado', 'executada')->update(['estado' => 'PAGA']);

        DB::statement("ALTER TABLE transacoes_despesas MODIFY estado ENUM('PENDENTE_CABIMENTADA','LIQUIDADA_APROVADA','PAGA') NOT NULL DEFAULT 'PENDENTE_CABIMENTADA'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE transacoes_despesas MODIFY estado VARCHAR(50) NOT NULL");
    }
};
