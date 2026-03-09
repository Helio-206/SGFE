<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabela Orcamento — Definição Orçamental (RF03)
     * Vínculo 1:1 com Instituição para o ano fiscal corrente.
     */
    public function up(): void
    {
        Schema::create('orcamentos', function (Blueprint $table) {
            $table->id('id_orcamento');
            $table->unsignedBigInteger('id_user');
            $table->unsignedBigInteger('id_inst');
            $table->decimal('valor_total', 15, 2);   // Restrição Crítica: decimal(15,2)
            $table->year('ano_fiscal')->default(2025); // Ano fiscal corrente (OGE 2025)
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('restrict');
            $table->foreign('id_inst')->references('id_inst')->on('instituicoes')->onDelete('restrict');

            // Garante 1 orçamento por instituição por ano fiscal
            $table->unique(['id_inst', 'ano_fiscal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orcamentos');
    }
};
