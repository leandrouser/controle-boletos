<?php

namespace App\Services\Boleto;

use Picqer\Barcode\BarcodeGeneratorSVG;

class BoletoCodigoService
{
    public function parseBrValue(string $valor): float
    {
        $valorNormalizado = trim($valor);

        if (str_contains($valorNormalizado, ',')) {
            $valorNormalizado = str_replace('.', '', $valorNormalizado);
            $valorNormalizado = str_replace(',', '.', $valorNormalizado);
        }

        return (float) $valorNormalizado;
    }

    public function extrairContaOrigem(string $linha): ?string
    {
        $tamanho = strlen($linha);

        if ($tamanho === 48 && str_starts_with($linha, '8')) {
            return $this->contaDeCodigo44($this->convenio48ParaCodigo44($linha));
        }

        if ($tamanho === 47 && str_starts_with($linha, '8')) {
            return $this->contaDeCodigo44($this->convenio47ParaCodigo44($linha));
        }

        if ($tamanho === 44 && str_starts_with($linha, '8')) {
            return $this->contaDeCodigo44($linha);
        }

        if ($tamanho === 47 && ! str_starts_with($linha, '8')) {
            $campoLivre = substr($linha, 4, 5)
                .substr($linha, 11, 10)
                .substr($linha, 22, 10);

            return substr($linha, 0, 3).substr($campoLivre, 0, 19);
        }

        if ($tamanho === 44 && ! str_starts_with($linha, '8')) {
            return substr($linha, 0, 3).substr($linha, 4, 19);
        }

        return null;
    }

    public function gerarBarcode(string $linha): array
    {
        $tamanho = strlen($linha);
        $aviso = null;

        if ($tamanho === 44) {
            $codigo44 = $linha;
            $tipo = str_starts_with($linha, '8') ? 'convenio' : 'bancario';
        } elseif ($tamanho === 47 && ! str_starts_with($linha, '8')) {
            $codigo44 = substr($linha, 0, 3)
                .substr($linha, 3, 1)
                .substr($linha, 32, 1)
                .substr($linha, 33, 14)
                .substr($linha, 4, 5)
                .substr($linha, 10, 10)
                .substr($linha, 21, 10);
            $tipo = 'bancario';
        } elseif ($tamanho === 48 && str_starts_with($linha, '8')) {
            $codigo44 = $this->convenio48ParaCodigo44($linha);
            $tipo = 'convenio';
        } elseif ($tamanho === 47 && str_starts_with($linha, '8')) {
            $codigo44 = $this->convenio47ParaCodigo44($linha);
            $tipo = 'convenio';
        } else {
            $codigo44 = $linha;
            $tipo = 'desconhecido';
            $aviso = "Código com {$tamanho} dígitos não reconhecido.";
        }

        $generator = new BarcodeGeneratorSVG;

        return [
            'barcode' => $generator->getBarcode(
                $codigo44,
                $generator::TYPE_INTERLEAVED_2_5,
                2,
                100
            ),
            'tamanho' => $tamanho,
            'tipo' => $tipo,
            'aviso' => $aviso,
            'codigo44' => $codigo44,
        ];
    }

    private function convenio48ParaCodigo44(string $linha): string
    {
        return substr($linha, 0, 11)
            .substr($linha, 12, 11)
            .substr($linha, 24, 11)
            .substr($linha, 36, 11);
    }

    private function convenio47ParaCodigo44(string $linha): string
    {
        return substr($linha, 0, 11)
            .substr($linha, 12, 11)
            .substr($linha, 24, 11)
            .substr($linha, 36, 11);
    }

    private function contaDeCodigo44(string $codigo44): string
    {
        $campoLivre = substr($codigo44, 15);

        return substr($codigo44, 0, 2).substr($campoLivre, 0, 20);
    }
}
