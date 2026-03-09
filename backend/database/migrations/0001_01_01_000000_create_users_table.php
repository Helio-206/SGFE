<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Tabela Instituicao — deve ser criada antes de users (FK)
        Schema::create('instituicoes', function (Blueprint $table) {
            $table->id('id_inst');
            $table->string('nome', 150);
            $table->string('tipo', 50);               // ex: Ministério, Administração Municipal
            $table->string('codigo', 20)->unique();    // Código de Unidade Orçamental (UO)
            $table->string('responsavel', 100);
            $table->timestamps();
        });

        // Tabela Usuario (users) — conforme MER + campos de segurança
        Schema::create('users', function (Blueprint $table) {
            $table->id('id_user');
            $table->string('nome', 100);
            $table->string('username', 50)->unique();
            $table->string('email', 100)->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password', 255);
            $table->enum('role', ['admin', 'gestor', 'auditor'])->default('gestor');
            $table->enum('status', ['ativo', 'inativo'])->default('ativo'); // RNF08 – Auditabilidade
            $table->unsignedBigInteger('id_inst');
            $table->foreign('id_inst')->references('id_inst')->on('instituicoes')->onDelete('restrict');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        Schema::dropIfExists('instituicoes');
    }
};
