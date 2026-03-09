<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add RUPE code to transacoes_receitas.
     */
    public function up(): void
    {
        Schema::table('transacoes_receitas', function (Blueprint $table) {
            $table->string('codigo_rupe', 40)->unique()->after('font_receita');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transacoes_receitas', function (Blueprint $table) {
            $table->dropUnique(['codigo_rupe']);
            $table->dropColumn('codigo_rupe');
        });
    }
};
