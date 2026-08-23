@extends('layouts.app')

@section('content')
<div class="container-fluid pb-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-0">Controle Financeiro</h2>
            <p class="text-muted">Gerencie seus boletos e fluxos de pagamento.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('boletos.relatorios') }}" class="btn btn-outline-primary px-3 shadow-sm" title="Relatórios">
                <i class="fas fa-chart-pie me-2"></i>Relatórios
            </a>
            <button id="theme-toggle" class="btn btn-outline-secondary px-3 shadow-sm" title="Alternar Tema">
                <i id="theme-toggle-icon" class="fas fa-moon"></i>
            </button>
            <a href="{{ route('boletos.create') }}" class="btn btn-primary px-4 shadow-sm fw-bold">
                <i class="fas fa-plus me-2"></i>Novo Boleto
            </a>
        </div>
    </div>

    {{-- Cards de resumo --}}
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div id="card-resumo" class="card border-0 shadow-sm text-white bg-dark h-100" style="cursor:pointer;transition:all 0.3s ease;">
                <div class="card-body d-flex flex-column justify-content-between">
                    <div class="d-flex justify-content-between align-items-center opacity-75">
                        <span id="titulo-resumo" class="text-uppercase small fw-bold">Total Hoje</span>
                        <i class="fas fa-sync-alt fa-sm"></i>
                    </div>
                    <div>
                        <h2 id="valor-resumo" class="fw-bold mb-0">R$ {{ number_format($totalDia, 2, ',', '.') }}</h2>
                        <small class="d-block mt-1">
                            <span id="qtd-resumo" class="badge bg-white text-dark">{{ $qtdDia }} {{ $qtdDia == 1 ? 'boleto' : 'boletos' }}</span>
                            <span id="legenda-resumo" class="ms-1 opacity-75 small">vencendo hoje + atrasados</span>
                        </small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-left:5px solid #dc3545 !important;">
                <div class="card-body">
                    <span class="text-uppercase small fw-bold text-muted">Atrasados (Geral)</span>
                    <h2 class="fw-bold text-danger mb-0">
                        R$ {{ number_format(\App\Models\Boleto::where('status','pendente')->where('data_vencimento','<',$hoje)->sum('valor'),2,',','.') }}
                    </h2>
                    <small class="text-muted">Total pendente fora do prazo</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-left:5px solid #198754 !important;">
                <div class="card-body">
                    <span class="text-uppercase small fw-bold text-muted">Total Exibido</span>
                    <h2 class="fw-bold text-success mb-0">R$ {{ number_format($boletos->sum('valor'),2,',','.') }}</h2>
                    <small class="text-muted">Soma dos registros desta pagina</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-light rounded">
            <form action="{{ route('dashboard') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted">Beneficiario</label>
                    <div style="position: relative;">
                        <input type="text" name="beneficiario" id="filtro-beneficiario"
                            class="form-control form-control-sm"
                            value="{{ request('beneficiario') }}" placeholder="Empresa...">
                        <ul id="autocomplete-list" style="
                            display: none; position: absolute; z-index: 1000;
                            background: white; border: 1px solid #ccc; width: 100%;
                            max-height: 200px; overflow-y: auto; list-style: none;
                            margin: 0; padding: 0; border-radius: 0 0 4px 4px;
                            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
                        "></ul>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted">Categoria</label>
                    <select name="categoria_id" class="form-select form-select-sm">
                        <option value="">Todas</option>
                        @foreach($categorias as $cat)
                            <option value="{{ $cat->id }}" {{ request('categoria_id') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->nome }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="pendente"   {{ request('status','pendente') == 'pendente'   ? 'selected' : '' }}>Pendentes</option>
                        <option value="vence_hoje" {{ request('status') == 'vence_hoje'            ? 'selected' : '' }}>Vence Hoje</option>
                        <option value="pago"       {{ request('status') == 'pago'                  ? 'selected' : '' }}>Pagos</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted">Inicio Periodo</label>
                    <input type="date" name="data_inicio" class="form-control form-control-sm" value="{{ request('data_inicio') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-bold small text-muted">Fim Periodo</label>
                    <input type="date" name="data_fim" class="form-control form-control-sm" value="{{ request('data_fim') }}">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-dark btn-sm flex-grow-1 fw-bold">
                        <i class="fas fa-filter me-1"></i> Filtrar
                    </button>
                    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm flex-grow-1">Limpar</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Tabela --}}
    <div class="card border-0 shadow-sm">
        <form action="{{ route('boletos.pagarLote') }}" method="POST" id="form-lote">
            @csrf
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Listagem de Boletos ({{ ucfirst($status) }}s)</h5>
                @if($status == 'pendente')
                    <button type="button" id="btn-pagar-lote"
                        class="btn btn-success btn-sm fw-bold shadow-sm no-loading"
                        onclick="confirmarPagamentoLote()" disabled>
                        <i class="fas fa-check-double me-1"></i> Pagar Selecionados
                    </button>
                @endif
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="bg-light text-muted small text-uppercase">
                        <tr>
                            <th class="border-0 ps-4 py-3" style="width:40px; vertical-align:middle;">
                                @if($status == 'pendente' || $status == 'vence_hoje')
                                    <input type="checkbox" id="select-all" class="form-check-input" style="display:block; margin:0 auto;">
                                @endif
                            </th>
                            <th class="border-0 py-3">Beneficiário</th>
                            <th class="border-0 py-3">Categoria</th>
                            <th class="border-0 py-3">Valor</th>
                            <th class="border-0 py-3">Vencimento</th>
                            <th class="border-0 py-3">Status</th>
                            <th class="border-0 pe-4 py-3 text-center">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($boletos as $boleto)
                            @php
                                $dataVencimento = \Carbon\Carbon::parse($boleto->data_vencimento)->startOfDay();
                                $isVenceHoje    = $dataVencimento->isToday() && $boleto->status == 'pendente';
                                $isVencido      = $dataVencimento->lt($hoje) && !$isVenceHoje && $boleto->status == 'pendente';
                            @endphp
                            <tr class="{{ $isVencido ? 'bg-danger-subtle' : ($isVenceHoje ? 'bg-warning-subtle' : '') }}">
                                <td class="ps-4" style="width:40px; vertical-align:middle;">
                                    @if($boleto->status == 'pendente')
                                        <input type="checkbox" name="ids[]"
                                            value="{{ $boleto->id }}"
                                            data-valor="{{ $boleto->valor }}"
                                            class="form-check-input boleto-checkbox"
                                            style="display:block; margin:0 auto;">
                                    @else
                                        <i class="fas fa-check-circle text-success opacity-50"></i>
                                    @endif
                                </td>
                                <td class="fw-bold">{{ $boleto->beneficiario }}</td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary border">
                                        {{ $boleto->categoria_label }}
                                    </span>
                                </td>
                                <td class="fw-bold text-primary">R$ {{ number_format($boleto->valor, 2, ',', '.') }}</td>
                                <td>
                                    <span class="{{ $isVencido ? 'text-danger fw-bold' : ($isVenceHoje ? 'text-warning fw-bold' : '') }}">
                                        <i class="far fa-calendar-alt me-1"></i>
                                        {{ date('d/m/Y', strtotime($boleto->data_vencimento)) }}
                                    </span>
                                </td>
                                <td>
                                    @if($boleto->status == 'pago')
                                        <span class="badge rounded-pill bg-success-subtle text-success px-3">Pago</span>
                                    @elseif($isVenceHoje)
                                        <span class="badge rounded-pill bg-warning-subtle text-warning px-3 border border-warning">⚡ Vence Hoje</span>
                                    @elseif($isVencido)
                                        <span class="badge rounded-pill bg-danger-subtle text-danger px-3">Vencido</span>
                                    @else
                                        <span class="badge rounded-pill bg-success-subtle text-success px-3">Pendente</span>
                                    @endif
                                </td>
                                <td class="pe-4 text-center">
                                    <div class="btn-group shadow-sm">
                                        @if($boleto->status == 'pendente')
                                            <button type="button" class="btn btn-sm btn-success" onclick="pagarBoleto({{ $boleto->id }}, '{{ addslashes($boleto->beneficiario) }}')">
                                                <i class="fas fa-check"></i>
                                            </button>
                                        @endif
                                        <a href="{{ route('boletos.show', $boleto->id) }}" class="btn btn-white btn-sm border-end"><i class="fas fa-barcode"></i></a>
                                        <a href="{{ route('boletos.edit', $boleto->id) }}" class="btn btn-white btn-sm text-primary border-end"><i class="fas fa-edit"></i></a>
                                        <button type="button" class="btn btn-white btn-sm text-danger" onclick="excluirBoleto({{ $boleto->id }})">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">Nenhum registro encontrado.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="card-footer bg-white py-3 border-top">
                <div class="d-flex justify-content-between align-items-center">
                    <small class="text-muted">
                        Mostrando <b>{{ $boletos->firstItem() ?? 0 }}</b> de <b>{{ $boletos->total() }}</b>
                    </small>
                    <div>{{ $boletos->links() }}</div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Formularios fora da tabela --}}
@foreach($boletos as $boleto)
    @if($boleto->status == 'pendente')
        <form id="form-pagar-{{ $boleto->id }}"
            action="{{ route('boletos.pagar', $boleto->id) }}"
            method="POST" style="display:none;">
            @csrf
        </form>
    @endif

    <form id="form-excluir-{{ $boleto->id }}"
        action="{{ route('boletos.destroy', $boleto->id) }}"
        method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
@endforeach

{{-- Barra sticky de selecao --}}
<div id="sticky-bar" class="d-none"
    style="position:fixed;bottom:0;left:0;right:0;z-index:1050;background:#1e293b;color:#fff;padding:14px 24px;box-shadow:0 -4px 20px rgba(0,0,0,0.25);">
    <div class="container-fluid d-flex align-items-center justify-content-between flex-wrap gap-3">
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-white text-dark fs-6 px-3 py-2" id="sticky-count">0 boletos</span>
            <div>
                <div class="text-white-50 small">Total selecionado</div>
                <div class="fw-bold fs-5" id="sticky-total">R$ 0,00</div>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-outline-light btn-sm px-4 no-loading"
                onclick="desmarcarTodos()">
                <i class="fas fa-times me-1"></i> Cancelar
            </button>
            <button type="button" class="btn btn-success px-4 fw-bold no-loading"
                onclick="confirmarPagamentoLote()">
                <i class="fas fa-check-double me-1"></i> Pagar Selecionados
            </button>
        </div>
    </div>
</div>

<script>
    const themeToggleBtn  = document.getElementById('theme-toggle');
    const themeToggleIcon = document.getElementById('theme-toggle-icon');

    if (document.documentElement.classList.contains('dark')) {
        themeToggleIcon.classList.replace('fa-moon', 'fa-sun');
    }
    themeToggleBtn.addEventListener('click', function () {
        if (document.documentElement.classList.contains('dark')) {
            document.documentElement.classList.remove('dark');
            localStorage.setItem('color-theme', 'light');
            themeToggleIcon.classList.replace('fa-sun', 'fa-moon');
        } else {
            document.documentElement.classList.add('dark');
            localStorage.setItem('color-theme', 'dark');
            themeToggleIcon.classList.replace('fa-moon', 'fa-sun');
        }
    });

    const cardResumo = document.getElementById('card-resumo');
    const dados = [
        { titulo: "Total Hoje",   valor: "R$ {{ number_format($totalDia,    2, ',', '.') }}", qtd: "{{ $qtdDia }} {{ $qtdDia == 1 ? 'boleto' : 'boletos' }}",       legenda: "vencendo hoje + atrasados",  classe: "bg-dark"    },
        { titulo: "Total Semana", valor: "R$ {{ number_format($totalSemana, 2, ',', '.') }}", qtd: "{{ $qtdSemana }} {{ $qtdSemana == 1 ? 'boleto' : 'boletos' }}", legenda: "ate domingo + atrasados",    classe: "bg-primary" },
        { titulo: "Total Mes",    valor: "R$ {{ number_format($totalMes,    2, ',', '.') }}", qtd: "{{ $qtdMes }} {{ $qtdMes == 1 ? 'boleto' : 'boletos' }}",       legenda: "ate fim do mes + atrasados", classe: "bg-info"    },
    ];
    let estadoAtual = 0;
    if (cardResumo) {
        cardResumo.addEventListener('click', function () {
            cardResumo.style.opacity = '0.5';
            setTimeout(() => {
                estadoAtual = (estadoAtual + 1) % dados.length;
                const info = dados[estadoAtual];
                document.getElementById('titulo-resumo').innerText = info.titulo;
                document.getElementById('valor-resumo').innerText  = info.valor;
                document.getElementById('qtd-resumo').innerText    = info.qtd;
                document.getElementById('legenda-resumo').innerText = info.legenda;
                cardResumo.className = `card border-0 shadow-sm text-white h-100 ${info.classe}`;
                cardResumo.style.opacity = '1';
            }, 150);
        });
    }

    function pagarBoleto(id, nome) {
        if (confirm('Confirmar pagamento de ' + nome + '?')) {
            document.getElementById('form-pagar-' + id).submit();
        }
    }

    function excluirBoleto(id) {
        if (confirm('Excluir este boleto?')) {
            document.getElementById('form-excluir-' + id).submit();
        }
    }

    const selectAll   = document.getElementById('select-all');
    const checkboxes  = document.querySelectorAll('.boleto-checkbox');
    const btnLote     = document.getElementById('btn-pagar-lote');
    const stickyBar   = document.getElementById('sticky-bar');
    const stickyCount = document.getElementById('sticky-count');
    const stickyTotal = document.getElementById('sticky-total');

    const brl = v => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(v);

    function atualizarSelecao() {
        const marcadas = document.querySelectorAll('.boleto-checkbox:checked');
        const count    = marcadas.length;
        let   soma     = 0;
        marcadas.forEach(cb => { soma += parseFloat(cb.getAttribute('data-valor')); });

        if (btnLote) {
            btnLote.disabled  = count === 0;
            btnLote.innerHTML = `<i class="fas fa-check-double me-1"></i> Pagar ${count} selecionados`;
        }

        if (count > 0) {
            stickyBar.classList.remove('d-none');
            stickyCount.textContent = `${count} ${count === 1 ? 'boleto' : 'boletos'}`;
            stickyTotal.textContent = brl(soma);
        } else {
            stickyBar.classList.add('d-none');
        }
    }

    function desmarcarTodos() {
        checkboxes.forEach(cb => cb.checked = false);
        if (selectAll) selectAll.checked = false;
        atualizarSelecao();
    }

    function confirmarPagamentoLote() {
        const marcadas = document.querySelectorAll('.boleto-checkbox:checked');
        const count    = marcadas.length;
        if (count === 0) return;
        const total = stickyTotal.textContent;
        if (confirm(`Confirmar pagamento de ${count} ${count === 1 ? 'boleto' : 'boletos'} totalizando ${total}?`)) {
            document.getElementById('form-lote').submit();
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(cb => cb.checked = this.checked);
            atualizarSelecao();
        });
    }
    checkboxes.forEach(cb => cb.addEventListener('change', atualizarSelecao));

    // Busca dinâmica por beneficiário
    let debounce;
    document.getElementById('filtro-beneficiario')?.addEventListener('input', function () {
        clearTimeout(debounce);
        debounce = setTimeout(() => {
            this.closest('form').submit();
        }, 500);
    });

    // Submit automático ao trocar status
    document.querySelector('select[name="status"]')?.addEventListener('change', function () {
        this.closest('form').submit();
    });
</script>
@endsection
