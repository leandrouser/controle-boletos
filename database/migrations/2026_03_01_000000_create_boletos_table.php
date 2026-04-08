<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('boletos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('beneficiario')->nullable();
            $table->string('codigo_barras')->nullable();
            $table->string('linha_digitavel')->nullable();
            $table->date('data_pagamento')->nullable();
            $table->string('assinatura_origem')->nullable();
            $table->decimal('valor', 10, 2);
            $table->date('data_vencimento');
            $table->string('status')->default('pendente');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('boletos');
    }
};
