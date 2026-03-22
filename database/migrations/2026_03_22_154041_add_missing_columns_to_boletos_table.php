<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{

    public function up(): void
{
    Schema::table('boletos', function (Blueprint $table) {
        if (!Schema::hasColumn('boletos', 'beneficiario')) {
            $table->string('beneficiario')->nullable()->after('user_id');
        }

        if (!Schema::hasColumn('boletos', 'assinatura_origem')) {
            $table->string('assinatura_origem')->nullable()->after('linha_digitavel');
        }

        if (!Schema::hasColumn('boletos', 'codigo_barras')) {
            $table->string('codigo_barras')->nullable();
        }

        if (Schema::hasColumn('boletos', 'vencimento') && !Schema::hasColumn('boletos', 'data_vencimento')) {
            $table->renameColumn('vencimento', 'data_vencimento');
        }
    });
}

    public function down(): void
    {
        Schema::table('boletos', function (Blueprint $table) {
            //
        });
    }
};
