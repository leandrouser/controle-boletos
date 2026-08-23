@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="fas fa-chart-pie me-2"></i> Relatórios</h4>
    <div class="d-flex gap-2">
        <a href="{{ route('boletos.relatorios.pdf', request()->query()) }}" class="btn btn-outline-danger btn-sm">
            <i class="fas fa-file-pdf me-1"></i> Exportar PDF
        </a>
        <a href="{{ route('boletos.relatorios.csv', request()->query()) }}" class="btn btn-outline-success btn-sm">
            <i class="fas fa-file-excel me-1"></i> Exportar Excel
        </a>
    </div>
</div>

{{-- ─── Filtro de período ─────────────────────────────────────────────── --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('boletos.relatorios') }}" method="GET" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted">Data Inicial</label>
                <input type="date" name="data_inicio" class="form-control"
                    value="{{ $dataInicio }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label small fw-bold text-muted">Data Final</label>
                <input type="date" name="data_fim" class="form-control"
                    value="{{ $dataFim }}" required>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill no-loading">
                    <i class="fas fa-search me-1"></i> Buscar
                </button>
                <a href="{{ route('boletos.relatorios') }}" class="btn btn-outline-secondary" title="Limpar filtro">
                    <i class="fas fa-rotate-left"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ─── Cards de resumo ───────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-3">
                <div class="small text-muted fw-bold text-uppercase mb-1" style="font-size:.7rem;">Pago</div>
                <div class="h5 mb-0 text-success">R$ {{ number_format($totalPago, 2, ',', '.') }}</div>
                <div class="small text-muted">{{ $qtdPago }} boleto(s)</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-3">
                <div class="small text-muted fw-bold text-uppercase mb-1" style="font-size:.7rem;">Pendente</div>
                <div class="h5 mb-0 text-warning">R$ {{ number_format($totalPendente, 2, ',', '.') }}</div>
                <div class="small text-muted">{{ $qtdPendente }} boleto(s)</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-body p-3">
                <div class="small text-muted fw-bold text-uppercase mb-1" style="font-size:.7rem;">Vencido</div>
                <div class="h5 mb-0 text-danger">R$ {{ number_format($totalVencido, 2, ',', '.') }}</div>
                <div class="small text-muted">{{ $qtdVencido }} boleto(s)</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card shadow-sm border-0 h-100 bg-primary text-white">
            <div class="card-body p-3">
                <div class="small fw-bold text-uppercase mb-1" style="font-size:.7rem;opacity:.85;">Total Geral</div>
                <div class="h5 mb-0">R$ {{ number_format($totalGeral, 2, ',', '.') }}</div>
                <div class="small" style="opacity:.85;">{{ $qtdPago + $qtdPendente + $qtdVencido }} boleto(s)</div>
                @if($variacaoPeriodo !== null)
                    <div class="small mt-1">
                        <span class="badge {{ $variacaoPeriodo >= 0 ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success' }}">
                            <i class="fas fa-arrow-{{ $variacaoPeriodo >= 0 ? 'up' : 'down' }} me-1"></i>
                            {{ abs($variacaoPeriodo) }}% vs período anterior
                        </span>
                    </div>
                @else
                    <div class="small mt-1" style="opacity:.7;">Sem dados no período anterior para comparar</div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ─── Gráficos ──────────────────────────────────────────────────────── --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <strong><i class="fas fa-chart-pie me-1 text-muted"></i> Distribuição por Status</strong>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center" style="min-height:280px;">
                @if($totalGeral > 0)
                    <canvas id="graficoStatus"></canvas>
                @else
                    <div class="text-muted text-center py-5">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        Nenhum boleto no período
                    </div>
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <strong><i class="fas fa-chart-pie me-1 text-muted"></i> Distribuição por Categoria</strong>
            </div>
            <div class="card-body d-flex align-items-center justify-content-center" style="min-height:280px;">
                @if($porCategoria->count() > 0)
                    <canvas id="graficoCategoria"></canvas>
                @else
                    <div class="text-muted text-center py-5">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        Nenhum boleto no período
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0 h-100">
            <div class="card-header bg-white">
                <strong><i class="fas fa-chart-bar me-1 text-muted"></i> Total por Mês</strong>
            </div>
            <div class="card-body" style="min-height:280px;">
                @if($porMes->count() > 0)
                    <canvas id="graficoPorMes"></canvas>
                @else
                    <div class="text-muted text-center py-5">
                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                        Nenhum boleto no período
                    </div>
                @endif
            </div>
            @if($porMes->count() > 1)
                <div class="table-responsive border-top">
                    <table class="table table-sm mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Mês</th>
                                <th class="text-center">Qtd</th>
                                <th class="text-end">Total</th>
                                <th class="text-end">Variação vs mês anterior</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($porMes as $mes)
                                <tr>
                                    <td>{{ $mes['label'] }}</td>
                                    <td class="text-center">{{ $mes['qtd'] }}</td>
                                    <td class="text-end">R$ {{ number_format($mes['total'], 2, ',', '.') }}</td>
                                    <td class="text-end">
                                        @if($mes['variacao'] === null)
                                            <span class="text-muted">—</span>
                                        @else
                                            <span class="{{ $mes['variacao'] >= 0 ? 'text-danger' : 'text-success' }}">
                                                <i class="fas fa-arrow-{{ $mes['variacao'] >= 0 ? 'up' : 'down' }} me-1"></i>
                                                {{ abs($mes['variacao']) }}%
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ─── Tabela detalhada ──────────────────────────────────────────────── --}}
<div class="card shadow-sm border-0">

    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <strong><i class="fas fa-list me-1 text-muted"></i> Boletos do Período</strong>
        <span class="badge bg-secondary-subtle text-secondary">{{ $boletos->count() }} registro(s)</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead class="table-light">
                <tr>
                    <th>Beneficiário</th>
                    <th>Categoria</th>
                    <th>Vencimento</th>
                    <th>Pagamento</th>
                    <th class="text-end">Valor</th>
                    <th class="text-center">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($boletos as $boleto)
                    @php
                        $ehVencido = $boleto->status === 'pendente' && \Carbon\Carbon::parse($boleto->data_vencimento)->format('Y-m-d') < $hoje;
                    @endphp
                    <tr>
                        <td>{{ $boleto->beneficiario ?? '—' }}</td>
                        <td><span class="badge bg-secondary-subtle text-secondary border">{{ $boleto->categoria_label }}</span></td>
                        <td>{{ \Carbon\Carbon::parse($boleto->data_vencimento)->format('d/m/Y') }}</td>
                        <td>{{ $boleto->data_pagamento ? \Carbon\Carbon::parse($boleto->data_pagamento)->format('d/m/Y') : '—' }}</td>
                        <td class="text-end">R$ {{ number_format($boleto->valor, 2, ',', '.') }}</td>
                        <td class="text-center">
                            @if($boleto->status === 'pago')
                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                    <i class="fas fa-check me-1"></i> Pago
                                </span>
                            @elseif($ehVencido)
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                    <i class="fas fa-triangle-exclamation me-1"></i> Vencido
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                    <i class="fas fa-clock me-1"></i> Pendente
                                </span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            Nenhum boleto encontrado no período selecionado.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const isDark = document.documentElement.classList.contains('dark');
    const corTexto = isDark ? '#e2e8f0' : '#334155';
    Chart.defaults.color = corTexto;
    Chart.defaults.font.family = "'Figtree', sans-serif";

    // ─── Gráfico de Pizza: distribuição por status ─────────────────────
    const canvasStatus = document.getElementById('graficoStatus');
    if (canvasStatus) {
        new Chart(canvasStatus, {
            type: 'pie',
            data: {
                labels: ['Pago', 'Pendente', 'Vencido'],
                datasets: [{
                    data: [
                        {{ $totalPago }},
                        {{ $totalPendente }},
                        {{ $totalVencido }}
                    ],
                    backgroundColor: ['#22c55e', '#eab308', '#ef4444'],
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                const valor = ctx.raw.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                                return `${ctx.label}: ${valor}`;
                            }
                        }
                    }
                }
            }
        });
    }

    // ─── Gráfico de Pizza: distribuição por categoria ──────────────────
    const canvasCategoria = document.getElementById('graficoCategoria');
    if (canvasCategoria) {
        const dadosPorCategoria = @json($porCategoria);

        // Paleta fixa para as categorias ficarem sempre com a mesma cor entre buscas
        const paletaCategorias = ['#3b82f6', '#f97316', '#22c55e', '#a855f7', '#ef4444',
                                   '#eab308', '#06b6d4', '#ec4899', '#84cc16', '#6b7280'];

        new Chart(canvasCategoria, {
            type: 'pie',
            data: {
                labels: dadosPorCategoria.map(c => c.label),
                datasets: [{
                    data: dadosPorCategoria.map(c => c.total),
                    backgroundColor: dadosPorCategoria.map((_, i) => paletaCategorias[i % paletaCategorias.length]),
                    borderWidth: 0,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { position: 'bottom' },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                const valor = ctx.raw.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                                return `${ctx.label}: ${valor}`;
                            }
                        }
                    }
                }
            }
        });
    }

    // ─── Gráfico de Barras: total por mês ───────────────────────────────
    const canvasMes = document.getElementById('graficoPorMes');
    if (canvasMes) {
        const dadosPorMes = @json($porMes);

        new Chart(canvasMes, {
            type: 'bar',
            data: {
                labels: dadosPorMes.map(m => m.label),
                datasets: [{
                    label: 'Total do mês',
                    data: dadosPorMes.map(m => m.total),
                    backgroundColor: '#3b82f6',
                    borderRadius: 6,
                    maxBarThickness: 48,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                const valor = ctx.raw.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                                return `Total: ${valor}`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: isDark ? '#27272a' : '#e2e8f0' },
                        ticks: {
                            callback: function (value) {
                                return 'R$ ' + value.toLocaleString('pt-BR');
                            }
                        }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }
});
</script>

@endsection
