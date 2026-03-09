<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adiciona id_classe (Classificação Económica) às despesas
     * para permitir relatórios agrupados por natureza (RF06).
     */
    public function up(): void
    {
        Schema::table('transacoes_despesas', function (Blueprint $table) {
            $table->unsignedBigInteger('id_classe')->nullable()->after('id_user');
            $table->foreign('id_classe')->references('id_classe')->on('classificacoes_economicas')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('transacoes_despesas', function (Blueprint $table) {
            $table->dropForeign(['id_classe']);
            $table->dropColumn('id_classe');
        });
    }
};
