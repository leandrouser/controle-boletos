<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsappAlertaService
{
    private string $url;
    private string $apiKey;
    private string $instance;

    /** @var string[] */
    private array $numeros;

    public function __construct()
    {
        $this->url      = rtrim(config('services.evolution.url'), '/');
        $this->apiKey   = config('services.evolution.key');
        $this->instance = config('services.evolution.instance');

        $numerosConfig = config('services.evolution.numeros', '');
        $this->numeros = array_filter(array_map('trim', explode(',', $numerosConfig)));
    }

    /**
     * Envia uma mensagem de texto para todos os números configurados.
     * Retorna um array associativo [numero => bool sucesso].
     */
    public function enviarParaTodos(string $mensagem): array
    {
        $resultados = [];

        foreach ($this->numeros as $numero) {
            $resultados[$numero] = $this->enviar($numero, $mensagem);
        }

        return $resultados;
    }

    /**
     * Envia uma mensagem de texto para um número específico via Evolution API.
     */
    public function enviar(string $numero, string $mensagem): bool
    {
        if (empty($this->url) || empty($this->apiKey) || empty($this->instance)) {
            Log::error('WhatsappAlertaService: configuração incompleta (url/key/instance ausentes).');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'apikey' => $this->apiKey,
            ])->post("{$this->url}/message/sendText/{$this->instance}", [
                'number' => $numero,
                'text'   => $mensagem,
            ]);

            if ($response->failed()) {
                Log::error('WhatsappAlertaService: falha ao enviar mensagem.', [
                    'numero'  => $numero,
                    'status'  => $response->status(),
                    'body'    => $response->body(),
                ]);
                return false;
            }

            return true;
        } catch (\Throwable $e) {
            Log::error('WhatsappAlertaService: exceção ao enviar mensagem.', [
                'numero' => $numero,
                'erro'   => $e->getMessage(),
            ]);
            return false;
        }
    }
}
