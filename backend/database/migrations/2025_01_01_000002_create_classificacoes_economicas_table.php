<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabela Classificacao_Economica — Catálogo de classificações (RF04)
     */
    public function up(): void
    {
        Schema::create('classificacoes_economicas', function (Blueprint $table) {
            $table->id('id_classe');
            $table->string('descricao', 100);
            $table->string('cod_classe', 20)->unique();  // Código da classificação económica
            $table->string('tipo_receita', 50);           // ex: Impostos, Taxas, Contribuições
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('classificacoes_economicas');
    }
};
