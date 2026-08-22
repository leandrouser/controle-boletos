<?php

namespace App\Services\Boleto;

use App\Models\BeneficiarioIdentificado;

class BeneficiarioService
{
    public function buscarPorAssinatura(string $assinatura): ?BeneficiarioIdentificado
    {
        return BeneficiarioIdentificado::where('assinatura', $assinatura)->first();
    }

    public function buscarPorConta(string $conta): ?BeneficiarioIdentificado
    {
        return BeneficiarioIdentificado::where('conta_origem', $conta)->first()
            ?? BeneficiarioIdentificado::where('assinatura', $conta)->first();
    }

    public function buscarSugestoes(string $termo)
    {
        return BeneficiarioIdentificado::where('nome_sugerido', 'like', '%'.$termo.'%')
            ->orderBy('nome_sugerido')
            ->limit(10)
            ->pluck('nome_sugerido')
            ->unique()
            ->values();
    }

    public function salvar(string $assinatura, string $contaOrigem, string $nome): void
    {
        BeneficiarioIdentificado::updateOrCreate(
            ['assinatura' => $assinatura],
            [
                'conta_origem' => $contaOrigem,
                'nome_sugerido' => $nome,
            ]
        );
    }
}
