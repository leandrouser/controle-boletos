<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Boleto extends Model
{
    protected $fillable = [
        'beneficiario',
        'valor',
        'data_vencimento',
        'codigo_barras',
        'status',
        'data_pagamento',
        'user_id',
        'linha_digitavel'
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