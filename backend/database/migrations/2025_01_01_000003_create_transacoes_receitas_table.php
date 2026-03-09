<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabela Transacao_Receita — Registro de Receitas (RF04)
     */
    public function up(): void
    {
        Schema::create('transacoes_receitas', function (Blueprint $table) {
            $table->id('id_receita');
            $table->string('font_receita', 150);          // Fonte da receita
            $table->date('data_registro');
            $table->decimal('valor_arrecadado', 15, 2);   // Restrição Crítica: decimal(15,2)
            $table->unsignedBigInteger('id_classe');
            $table->unsignedBigInteger('id_inst');         // Vínculo institucional para escopo

            $table->foreign('id_classe')->references('id_classe')->on('classificacoes_economicas')->onDelete('restrict');
            $table->foreign('id_inst')->references('id_inst')->on('instituicoes')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transacoes_receitas');
    }
};
