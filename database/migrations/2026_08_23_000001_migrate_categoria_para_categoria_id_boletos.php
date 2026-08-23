<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('boletos', function (Blueprint $table) {
            $table->foreignId('categoria_id')->nullable()->after('categoria')
                ->constrained('categorias')->nullOnDelete();
        });

        $mapa = [
            'agua'       => 'Água',
            'energia'    => 'Energia Elétrica',
            'internet'   => 'Internet / Telefone',
            'aluguel'    => 'Aluguel',
            'cartao'     => 'Cartão de Crédito',
            'mercado'    => 'Mercado / Alimentação',
            'transporte' => 'Transporte',
            'saude'      => 'Saúde',
            'educacao'   => 'Educação',
            'outros'     => 'Outros',
        ];

        $categorias = DB::table('categorias')->pluck('id', 'nome');

        foreach ($mapa as $chaveAntiga => $nomeNovo) {
            if (isset($categorias[$nomeNovo])) {
                DB::table('boletos')
                    ->where('categoria', $chaveAntiga)
                    ->update(['categoria_id' => $categorias[$nomeNovo]]);
            }
        }

        Schema::table('boletos', function (Blueprint $table) {
            $table->dropColumn('categoria');
        });
    }

    public function down(): void
    {
        Schema::table('boletos', function (Blueprint $table) {
            $table->string('categoria')->nullable()->after('categoria_id');
        });

        Schema::table('boletos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('categoria_id');
        });
    }
};
