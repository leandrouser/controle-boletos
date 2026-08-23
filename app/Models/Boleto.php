<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Boleto extends Model
{
    use SoftDeletes;

    protected $fillable = [
    'user_id',
    'beneficiario',
    'categoria_id',
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

public function categoria()
{
    return $this->belongsTo(Categoria::class);
}

// Retorna o nome legível da categoria. Se o boleto não tiver
// categoria vinculada (ou ela tiver sido excluída), cai em 'Sem categoria'.
public function getCategoriaLabelAttribute(): string
{
    return $this->categoria?->nome ?? 'Sem categoria';
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
