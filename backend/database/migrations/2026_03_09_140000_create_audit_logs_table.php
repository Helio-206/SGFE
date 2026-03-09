<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_user')->nullable();
            $table->string('acao', 120);
            $table->string('ip_address', 45)->nullable();
            $table->json('contexto')->nullable();
            $table->timestamps();

            $table->foreign('id_user')->references('id_user')->on('users')->nullOnDelete();
            $table->index(['acao', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
