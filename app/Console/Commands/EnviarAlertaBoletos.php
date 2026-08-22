<?php

namespace App\Console\Commands;

use App\Models\Boleto;
use App\Services\WhatsappAlertaService;
use Carbon\Carbon;
use Illuminate\Console\Command;

class EnviarAlertaBoletos extends Command
{
    /**
     * php artisan boletos:alerta resumo   -> hoje + amanhã + atrasados (manhã)
     * php artisan boletos:alerta urgente  -> só o que vence hoje e continua pendente (fim do dia)
     */
    protected $signature = 'boletos:alerta {tipo=resumo : resumo ou urgente}';

    protected $description = 'Envia alerta de boletos via WhatsApp (Evolution API)';

    public function handle(WhatsappAlertaService $whatsapp): int
    {
        $tipo = $this->argument('tipo');

        $mensagem = $tipo === 'urgente'
            ? $this->montarMensagemUrgente()
            : $this->montarMensagemResumo();

        if ($mensagem === null) {
            $this->info('Nenhum boleto para alertar. Nenhuma mensagem enviada.');
            return self::SUCCESS;
        }

        $resultados = $whatsapp->enviarParaTodos($mensagem);

        foreach ($resultados as $numero => $sucesso) {
            $sucesso
                ? $this->info("Enviado com sucesso para {$numero}")
                : $this->error("Falha ao enviar para {$numero} (veja storage/logs/laravel.log)");
        }

        return in_array(false, $resultados, true) ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Resumo diário: vencendo hoje, amanhã e atrasados.
     */
    private function montarMensagemResumo(): ?string
    {
        $hoje    = Carbon::today();
        $amanha  = Carbon::tomorrow();

        $vencemHoje = Boleto::where('status', 'pendente')
            ->whereDate('data_vencimento', $hoje)
            ->orderBy('valor', 'desc')
            ->get();

        $vencemAmanha = Boleto::where('status', 'pendente')
            ->whereDate('data_vencimento', $amanha)
            ->orderBy('valor', 'desc')
            ->get();

        $atrasados = Boleto::where('status', 'pendente')
            ->whereDate('data_vencimento', '<', $hoje)
            ->orderBy('data_vencimento', 'asc')
            ->get();

        if ($vencemHoje->isEmpty() && $vencemAmanha->isEmpty() && $atrasados->isEmpty()) {
            return null;
        }

        $linhas = [];
        $linhas[] = "📋 *Resumo de Boletos — " . $hoje->format('d/m/Y') . "*";
        $linhas[] = "";

        if ($atrasados->isNotEmpty()) {
            $linhas[] = "🔴 *Atrasados (" . $atrasados->count() . ")*";
            foreach ($atrasados as $b) {
                $diasAtraso = Carbon::parse($b->data_vencimento)->diffInDays($hoje);
                $linhas[] = "• {$b->beneficiario} — R$ " . number_format($b->valor, 2, ',', '.')
                    . " (venceu há {$diasAtraso} " . ($diasAtraso == 1 ? 'dia' : 'dias') . ")";
            }
            $linhas[] = "Total atrasado: R$ " . number_format($atrasados->sum('valor'), 2, ',', '.');
            $linhas[] = "";
        }

        if ($vencemHoje->isNotEmpty()) {
            $linhas[] = "⚠️ *Vencem hoje (" . $vencemHoje->count() . ")*";
            foreach ($vencemHoje as $b) {
                $linhas[] = "• {$b->beneficiario} — R$ " . number_format($b->valor, 2, ',', '.');
            }
            $linhas[] = "Total hoje: R$ " . number_format($vencemHoje->sum('valor'), 2, ',', '.');
            $linhas[] = "";
        }

        if ($vencemAmanha->isNotEmpty()) {
            $linhas[] = "🟡 *Vencem amanhã (" . $vencemAmanha->count() . ")*";
            foreach ($vencemAmanha as $b) {
                $linhas[] = "• {$b->beneficiario} — R$ " . number_format($b->valor, 2, ',', '.');
            }
            $linhas[] = "Total amanhã: R$ " . number_format($vencemAmanha->sum('valor'), 2, ',', '.');
            $linhas[] = "";
        }

        $totalGeral = $atrasados->sum('valor') + $vencemHoje->sum('valor') + $vencemAmanha->sum('valor');
        $linhas[] = "💰 *Total geral: R$ " . number_format($totalGeral, 2, ',', '.') . "*";

        return implode("\n", $linhas);
    }

    /**
     * Aviso urgente: só o que vence hoje e ainda está pendente (disparado no fim do dia).
     */
    private function montarMensagemUrgente(): ?string
    {
        $hoje = Carbon::today();

        $vencemHoje = Boleto::where('status', 'pendente')
            ->whereDate('data_vencimento', $hoje)
            ->orderBy('valor', 'desc')
            ->get();

        if ($vencemHoje->isEmpty()) {
            return null;
        }

        $linhas = [];
        $linhas[] = "⏰ *Último aviso — boletos vencendo HOJE e ainda pendentes*";
        $linhas[] = "";

        foreach ($vencemHoje as $b) {
            $linhas[] = "• {$b->beneficiario} — R$ " . number_format($b->valor, 2, ',', '.');
        }

        $linhas[] = "";
        $linhas[] = "💰 *Total: R$ " . number_format($vencemHoje->sum('valor'), 2, ',', '.') . "*";

        return implode("\n", $linhas);
    }
}
