<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <title>Relatório de Boletos</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        h1 { font-size: 18px; margin-bottom: 2px; }
        .subtitulo { color: #64748b; margin-bottom: 18px; }

        .resumo { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .resumo td {
            width: 25%; padding: 10px; border: 1px solid #e2e8f0;
            text-align: center; vertical-align: top;
        }
        .resumo .label { font-size: 9px; text-transform: uppercase; color: #64748b; }
        .resumo .valor { font-size: 15px; font-weight: bold; margin-top: 4px; }
        .cor-pago     { color: #16a34a; }
        .cor-pendente { color: #ca8a04; }
        .cor-vencido  { color: #dc2626; }
        .cor-total    { color: #1d4ed8; }

        h2 { font-size: 13px; margin-top: 22px; margin-bottom: 8px; border-bottom: 2px solid #e2e8f0; padding-bottom: 4px; }

        table.dados { width: 100%; border-collapse: collapse; }
        table.dados th {
            background: #f1f5f9; text-align: left; padding: 6px 8px;
            font-size: 9px; text-transform: uppercase; color: #475569;
            border-bottom: 1px solid #cbd5e1;
        }
        table.dados td {
            padding: 6px 8px; border-bottom: 1px solid #e2e8f0;
        }
        table.dados .text-right { text-align: right; }
        table.dados .text-center { text-align: center; }

        .badge {
            display: inline-block; padding: 2px 8px; border-radius: 10px;
            font-size: 9px; font-weight: bold;
        }
        .badge-pago     { background: #dcfce7; color: #16a34a; }
        .badge-pendente { background: #fef9c3; color: #ca8a04; }
        .badge-vencido  { background: #fee2e2; color: #dc2626; }

        .variacao-positiva { color: #dc2626; }
        .variacao-negativa { color: #16a34a; }

        .rodape { margin-top: 24px; font-size: 9px; color: #94a3b8; text-align: center; }
    </style>
</head>
<body>

    <h1>Relatório de Boletos</h1>
    <div class="subtitulo">
        Período: {{ \Carbon\Carbon::parse($dataInicio)->format('d/m/Y') }} a {{ \Carbon\Carbon::parse($dataFim)->format('d/m/Y') }}
        — Gerado em {{ now()->format('d/m/Y H:i') }}
    </div>

    {{-- Resumo --}}
    <table class="resumo">
        <tr>
            <td>
                <div class="label">Pago</div>
                <div class="valor cor-pago">R$ {{ number_format($totalPago, 2, ',', '.') }}</div>
                <div class="label">{{ $qtdPago }} boleto(s)</div>
            </td>
            <td>
                <div class="label">Pendente</div>
                <div class="valor cor-pendente">R$ {{ number_format($totalPendente, 2, ',', '.') }}</div>
                <div class="label">{{ $qtdPendente }} boleto(s)</div>
            </td>
            <td>
                <div class="label">Vencido</div>
                <div class="valor cor-vencido">R$ {{ number_format($totalVencido, 2, ',', '.') }}</div>
                <div class="label">{{ $qtdVencido }} boleto(s)</div>
            </td>
            <td>
                <div class="label">Total Geral</div>
                <div class="valor cor-total">R$ {{ number_format($totalGeral, 2, ',', '.') }}</div>
                @if($variacaoPeriodo !== null)
                    <div class="label {{ $variacaoPeriodo >= 0 ? 'variacao-positiva' : 'variacao-negativa' }}">
                        {{ $variacaoPeriodo >= 0 ? '▲' : '▼' }} {{ abs($variacaoPeriodo) }}% vs período anterior
                    </div>
                @endif
            </td>
        </tr>
    </table>

    {{-- Comparativo por categoria --}}
    @if($porCategoria->count() > 0)
        <h2>Distribuição por Categoria</h2>
        <table class="dados">
            <thead>
                <tr>
                    <th>Categoria</th>
                    <th class="text-center">Qtd</th>
                    <th class="text-right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($porCategoria as $cat)
                    <tr>
                        <td>{{ $cat['label'] }}</td>
                        <td class="text-center">{{ $cat['qtd'] }}</td>
                        <td class="text-right">R$ {{ number_format($cat['total'], 2, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Comparativo mês a mês --}}
    @if($porMes->count() > 0)
        <h2>Total por Mês (comparativo)</h2>
        <table class="dados">
            <thead>
                <tr>
                    <th>Mês</th>
                    <th class="text-center">Qtd</th>
                    <th class="text-right">Total</th>
                    <th class="text-right">Variação</th>
                </tr>
            </thead>
            <tbody>
                @foreach($porMes as $mes)
                    <tr>
                        <td>{{ $mes['label'] }}</td>
                        <td class="text-center">{{ $mes['qtd'] }}</td>
                        <td class="text-right">R$ {{ number_format($mes['total'], 2, ',', '.') }}</td>
                        <td class="text-right">
                            @if($mes['variacao'] === null)
                                —
                            @else
                                <span class="{{ $mes['variacao'] >= 0 ? 'variacao-positiva' : 'variacao-negativa' }}">
                                    {{ $mes['variacao'] >= 0 ? '▲' : '▼' }} {{ abs($mes['variacao']) }}%
                                </span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- Lista detalhada --}}
    <h2>Boletos do Período ({{ $boletos->count() }})</h2>
    <table class="dados">
        <thead>
            <tr>
                <th>Beneficiário</th>
                <th>Categoria</th>
                <th>Vencimento</th>
                <th>Pagamento</th>
                <th class="text-right">Valor</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($boletos as $boleto)
                @php
                    $ehVencido = $boleto->status === 'pendente' && $boleto->data_vencimento->format('Y-m-d') < $hoje;
                    $statusLabel = $boleto->status === 'pago' ? 'Pago' : ($ehVencido ? 'Vencido' : 'Pendente');
                    $statusClasse = $boleto->status === 'pago' ? 'badge-pago' : ($ehVencido ? 'badge-vencido' : 'badge-pendente');
                @endphp
                <tr>
                    <td>{{ $boleto->beneficiario ?? '—' }}</td>
                    <td>{{ $boleto->categoria_label }}</td>
                    <td>{{ $boleto->data_vencimento->format('d/m/Y') }}</td>
                    <td>{{ $boleto->data_pagamento ? $boleto->data_pagamento->format('d/m/Y') : '—' }}</td>
                    <td class="text-right">R$ {{ number_format($boleto->valor, 2, ',', '.') }}</td>
                    <td class="text-center"><span class="badge {{ $statusClasse }}">{{ $statusLabel }}</span></td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Nenhum boleto encontrado no período.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="rodape">Relatório gerado automaticamente pelo Controle de Boletos</div>

</body>
</html>
