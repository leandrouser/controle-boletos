<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
{
    Schema::create('boletos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->string('linha_digitavel');
        $table->decimal('valor', 10, 2);
        $table->date('vencimento');
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('boletos');
    }
};
