<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table("beneficiarios_identificados", function (Blueprint $table) {
            $table->string("conta_origem", 30)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table("beneficiarios_identificados", function (Blueprint $table) {
            $table->string("conta_origem", 50)->nullable()->change();
        });
    }
};
