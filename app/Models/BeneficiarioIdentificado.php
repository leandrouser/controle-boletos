<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BeneficiarioIdentificado extends Model
{
    protected $table = 'beneficiarios_identificados';

    protected $fillable = [
        'assinatura',
        'conta_origem',
        'nome_sugerido',
    ];

    public static function buscarPorAssinatura(string $assinatura): ?self
    {
        return static::where('assinatura', $assinatura)->first();
    }

    public static function buscarPorConta(string $conta): ?self
    {
        return static::where('conta_origem', $conta)->first();
    }
}
