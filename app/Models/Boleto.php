<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Boleto extends Model
{
    protected $fillable = [
    'user_id',
    'beneficiario',
    'codigo_barras',
    'linha_digitavel',
    'valor',
    'data_vencimento',
    'data_pagamento',
    'assinatura_origem',
    'status',
    ];

    public function user()
{
    return $this->belongsTo(User::class);
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
