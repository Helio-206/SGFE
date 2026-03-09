<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tabela Transacao_Despesas — Execução de Despesas (RF05)
     */
    public function up(): void
    {
        Schema::create('transacoes_despesas', function (Blueprint $table) {
            $table->id('id_despesa');
            $table->enum('estado', ['PENDENTE_CABIMENTADA', 'LIQUIDADA_APROVADA', 'PAGA'])
                ->default('PENDENTE_CABIMENTADA');
            $table->string('descricao', 150);
            $table->decimal('valor_bruto', 15, 2);       // Restrição Crítica: decimal(15,2)
            $table->date('data_registro');
            $table->unsignedBigInteger('id_inst');
            $table->unsignedBigInteger('id_user');        // Quem registrou a despesa

            $table->foreign('id_inst')->references('id_inst')->on('instituicoes')->onDelete('restrict');
            $table->foreign('id_user')->references('id_user')->on('users')->onDelete('restrict');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transacoes_despesas');
    }
};
