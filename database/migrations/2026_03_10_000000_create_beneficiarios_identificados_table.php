<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('beneficiarios_identificados', function (Blueprint $table) {
            $table->id();
            $table->string('assinatura')->unique();
            $table->string('nome_sugerido');
            $table->string('conta_origem', 50)->nullable();
            $table->index('conta_origem');
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('beneficiarios_identificados');
    }
};
