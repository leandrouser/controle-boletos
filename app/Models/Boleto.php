<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Boleto extends Model
{
    // Lembra de liberar os campos para salvamento em massa
    protected $fillable = [
        'beneficiario',
        'valor',
        'data_vencimento',
        'codigo_barras',
        'status',
        'data_pagamento',
        'user_id'
    ];

    public function user()
{
    return $this->belongsTo(User::class);
}
}