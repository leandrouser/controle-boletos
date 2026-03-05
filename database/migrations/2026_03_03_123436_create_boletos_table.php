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
    Schema::create('boletos', function (Blueprint $table) {
        $table->id();
        $table->string('beneficiario');
        $table->decimal('valor', 10, 2);
        $table->date('data_vencimento');
        $table->date('data_pagamento')->nullable();
        $table->text('codigo_barras');
        $table->string('status')->default('pendente');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('boletos');
    }
};
