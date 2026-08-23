<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Boleto extends Model
{
    use SoftDeletes;

    // Categorias disponíveis para classificação dos boletos.
    // Centralizado aqui para ser usado nos formulários (create/edit) e no relatório.
    public const CATEGORIAS = [
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

    protected $fillable = [
    'user_id',
    'beneficiario',
    'categoria',
    'codigo_barras',
    'linha_digitavel',
    'valor',
    'data_vencimento',
    'data_pagamento',
    'assinatura_origem',
    'status',
    ];

    protected $casts = [
        'data_vencimento' => 'date',
        'data_pagamento'  => 'date',
    ];

    public function user()
{
    return $this->belongsTo(User::class);
}

// Retorna o nome legível da categoria (ex: 'energia' -> 'Energia Elétrica').
// Se não tiver categoria definida, cai em 'Outros'.
public function getCategoriaLabelAttribute(): string
{
    return self::CATEGORIAS[$this->categoria] ?? 'Outros';
}

public function setValorAttribute($value)
    {
        if (is_string($value)) {
            if (str_contains($value, ',')) {
            $valorLimpo = str_replace('.', '', $value);
            $valorLimpo = str_replace(',', '.', $valorLimpo);
            $this->attributes['valor'] = (float) $valorLimpo;
        } else {
            $this->attributes['valor'] = (float) $value;
        }
    } else {
        $this->attributes['valor'] = $value;
    }
    }
}
