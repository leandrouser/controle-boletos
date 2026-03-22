<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('beneficiarios_identificados', function (Blueprint $table) {
            $table->string('conta_origem', 50)->nullable()->after('assinatura');
            $table->index('conta_origem');
        });
    }

    public function down(): void
    {
        Schema::table('beneficiarios_identificados', function (Blueprint $table) {
            $table->dropIndex(['conta_origem']);
            $table->dropColumn('conta_origem');
        });
    }
};
