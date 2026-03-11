<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up() {
    Schema::create('beneficiarios_identificados', function (Blueprint $table) {
        $table->id();
        $table->string('assinatura')->unique();
        $table->string('nome_sugerido');
        $table->timestamps();
    });

    Schema::table('boletos', function (Blueprint $table) {
        $table->string('assinatura_origem')->nullable()->after('beneficiario');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assinaturas_boletos');
    }
};
