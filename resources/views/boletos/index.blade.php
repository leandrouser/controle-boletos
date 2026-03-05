@extends('layouts.app')

@section('content')
<div class="container-fluid pb-5">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Controle Financeiro</h2>
            <p class="text-muted">Gerencie seus boletos e fluxos de pagamento.</p>
        </div>
        <a href="{{ route('boletos.create') }}" class="btn btn-primary px-4 shadow-sm fw-bold">
            <i class="fas fa-plus me-2"></i>Novo Boleto
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div id="card-resumo" class="card border-0 shadow-sm text-white bg-dark h-100" style="cursor: pointer; transition: all 0.3s ease; min-height: 130px;">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center opacity-75">
                        <span id="titulo-resumo" class="text-uppercase small fw-bold">Total Hoje</span>
                        <i class="fas fa-sync-alt fa-sm"></i>
                    </div>
                    <div>
                        <h2 id="valor-resumo" class="fw-bold mb-0">R$ {{ number_format($totalDia, 2, ',', '.') }}</h2>
                        <small id="legenda-resumo" class="opacity-75">A pagar nesta data</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-left: 5px solid #dc3545 !important;">
                <div class="card-body">
                    <span class="text-uppercase small fw-bold text-muted">Atrasados</span>
                    <h2 class="fw-bold text-danger mb-0">R$ {{ number_format($boletos->where('status', 'pendente')->where('data_vencimento', '<', $hoje)->sum('valor'), 2, ',', '.') }}</h2>
                    <small class="text-muted">Ação imediata necessária</small>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-left: 5px solid #198754 !important;">
                <div class="card-body">
                    <span class="text-uppercase small fw-bold text-muted">Total Pago</span>
                    <h2 class="fw-bold text-success mb-0">R$ {{ number_format($boletos->where('status', 'pago')->sum('valor'), 2, ',', '.') }}</h2>
                    <small class="text-muted">Consolidado do período</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-light rounded">
    <form action="{{ route('dashboard') }}" method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
        <label class="form-label fw-bold small text-muted">Beneficiário</label>
        <div class="input-group input-group-sm">
            <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
            <input type="text" name="beneficiario" class="form-control border-start-0" value="{{ request('beneficiario') }}" placeholder="Nome da empresa...">
        </div>
    </div>
    <div class="col-md-2">
        <label class="form-label fw-bold small text-muted">Valor</label>
        <input type="number" step="0.01" name="valor" class="form-control form-control-sm" value="{{ request('valor') }}" placeholder="0.00">
    </div>
    <div class="col-md-3">
        <label class="form-label fw-bold small text-muted">Vencimento</label>
        <input type="date" name="data_vencimento" class="form-control form-control-sm" value="{{ request('data_vencimento') }}">
    </div>
    
    <div class="col-md-4 d-flex gap-2">
        <button type="submit" class="btn btn-dark btn-sm flex-grow-1 fw-bold">
            <i class="fas fa-filter me-1"></i> Filtrar
        </button>
        
        <a href="{{ route('boletos.pdf', request()->all()) }}" class="btn btn-danger btn-sm flex-grow-1 fw-bold text-decoration-none d-flex align-items-center justify-content-center">
            <i class="fas fa-file-pdf me-1"></i> PDF
        </a>

        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm flex-grow-1 text-decoration-none d-flex align-items-center justify-content-center">
    Limpar
</a>
    </div>
</form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 ps-4 py-3 text-muted small text-uppercase">Beneficiário</th>
                            <th class="border-0 py-3 text-muted small text-uppercase">Valor</th>
                            <th class="border-0 py-3 text-muted small text-uppercase">Vencimento</th>
                            <th class="border-0 py-3 text-muted small text-uppercase">Status</th>
                            <th class="border-0 pe-4 py-3 text-center text-muted small text-uppercase">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($boletos as $boleto)
                            @php 
                                $isVencido = $boleto->data_vencimento < $hoje && $boleto->status == 'pendente';
                            @endphp
                            <tr>
                                <td class="ps-4 fw-bold text-dark">{{ $boleto->beneficiario }}</td>
                                <td class="fw-bold">R$ {{ number_format($boleto->valor, 2, ',', '.') }}</td>
                                <td>
                                    <span class="{{ $isVencido ? 'text-danger fw-bold' : '' }}">
                                        <i class="far fa-calendar-alt me-1"></i>
                                        {{ date('d/m/Y', strtotime($boleto->data_vencimento)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($boleto->status == 'pago')
                                        <span class="badge rounded-pill bg-success-subtle text-success px-3">Pago</span>
                                    @elseif($isVencido)
                                        <span class="badge rounded-pill bg-danger-subtle text-danger px-3">Vencido</span>
                                    @else
                                        <span class="badge rounded-pill bg-warning-subtle text-warning px-3">Pendente</span>
                                    @endif
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="btn-group shadow-sm">
                                        @if($boleto->status == 'pendente')
                                            <form action="{{ route('boletos.pagar', $boleto->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-white btn-sm text-success border-end" title="Dar Baixa">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            </form>
                                        @endif
                                        <a href="{{ route('boletos.edit', $boleto->id) }}" class="btn btn-white btn-sm text-primary border-end" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ route('boletos.destroy', $boleto->id) }}" method="POST" onsubmit="return confirm('Excluir este boleto permanentemente?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-white btn-sm text-danger" title="Excluir">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fa-3x mb-3 opacity-25"></i>
                                    <p class="mb-0">Nenhum boleto encontrado para os filtros aplicados.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos adicionais para o toque profissional */
    .bg-success-subtle { background-color: #e1f2e8 !important; }
    .bg-danger-subtle { background-color: #fce8e8 !important; }
    .bg-warning-subtle { background-color: #fff8e6 !important; }
    .btn-white { background: #fff; border: 1px solid #dee2e6; }
    .btn-white:hover { background: #f8f9fa; }
    .table-hover tbody tr:hover { background-color: #fbfbfb; }
    #card-resumo:hover { transform: translateY(-3px); filter: brightness(1.1); }
</style>

<script>
    const card = document.getElementById('card-resumo');
    const titulo = document.getElementById('titulo-resumo');
    const valor = document.getElementById('valor-resumo');
    const legenda = document.getElementById('legenda-resumo');

    const dados = [
        { titulo: "Total Hoje", valor: "R$ {{ number_format($totalDia, 2, ',', '.') }}", legenda: "A pagar nesta data", classe: "bg-dark" },
        { titulo: "Total Semana", valor: "R$ {{ number_format($totalSemana, 2, ',', '.') }}", legenda: "Segunda a Domingo", classe: "bg-primary" },
        { titulo: "Total Mês", valor: "R$ {{ number_format($totalMes, 2, ',', '.') }}", legenda: "Total do mês atual", classe: "bg-secondary" }
    ];

    let estadoAtual = 0;

    card.addEventListener('click', function() {
        estadoAtual = (estadoAtual + 1) % dados.length;
        const info = dados[estadoAtual];

        card.style.opacity = '0.7';
        card.style.transform = 'scale(0.98)';
        
        setTimeout(() => {
            titulo.innerText = info.titulo;
            valor.innerText = info.valor;
            legenda.innerText = info.legenda;
            
            card.classList.remove('bg-dark', 'bg-primary', 'bg-secondary');
            card.classList.add(info.classe);
            
            card.style.opacity = '1';
            card.style.transform = 'scale(1)';
        }, 150);
    });
</script>
@endsection