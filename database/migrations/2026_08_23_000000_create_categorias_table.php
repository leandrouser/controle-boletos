<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('categorias', function (Blueprint $table) {
            $table->id();
            $table->string('nome')->unique();
            $table->timestamps();
        });

        // Popula com as categorias que já existiam fixas no código (Boleto::CATEGORIAS)
        $agora = now();
        DB::table('categorias')->insert([
            ['nome' => 'Água',                  'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Energia Elétrica',       'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Internet / Telefone',    'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Aluguel',                'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Cartão de Crédito',      'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Mercado / Alimentação',  'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Transporte',             'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Saúde',                  'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Educação',               'created_at' => $agora, 'updated_at' => $agora],
            ['nome' => 'Outros',                 'created_at' => $agora, 'updated_at' => $agora],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias');
    }
};
