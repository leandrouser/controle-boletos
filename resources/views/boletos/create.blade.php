@extends('layouts.app')

@section('content')

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-primary text-white p-3">
                <h4 class="mb-0"><i class="fas fa-barcode me-2"></i> Novo Boleto</h4>
                <small>Preencha os dados ou utilize o leitor no código de barras</small>
            </div>
            <div class="card-body p-4">

                @if(session('success'))
                    <div class="alert alert-success border-0 shadow-sm d-flex align-items-center" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                <form action="{{ route('boletos.store') }}" method="POST" id="form-boleto">
                    @csrf

                    <input type="hidden" name="assinatura_origem" id="assinatura_origem">
                    <input type="hidden" name="conta_origem"      id="conta_origem">

                    <div class="mb-4 p-3 bg-light border-start border-primary border-4 rounded">
                        <label class="form-label fw-bold text-primary">
                            <i class="fas fa-expand me-1"></i> Linha Digitável / Código de Barras
                        </label>
                        <div class="input-group">
                            <input type="text" id="codigo_barras" name="codigo_barras"
                                class="form-control form-control-lg shadow-sm"
                                placeholder="Cole o código aqui para preenchimento automático..."
                                oninput="decifrarBoleto(this.value)" autofocus>
                            <span class="input-group-text bg-white" id="spinner-beneficiario" style="display:none;">
                                <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                            </span>
                        </div>
                        <div id="badge-tipo-boleto" class="mt-2" style="display:none;">
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 small">
                                <i class="fas fa-info-circle me-1"></i>
                                <span id="texto-tipo-boleto"></span>
                            </span>
                        </div>
                        <div id="badge-beneficiario-encontrado" class="mt-2" style="display:none;">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-2">
                                <i class="fas fa-history me-1"></i>
                                Beneficiário identificado pelo histórico:
                                <strong id="badge-nome-beneficiario"></strong>
                            </span>
                        </div>
                    </div>

                    <hr class="text-muted mb-4">

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Beneficiário / Empresa</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-building text-muted"></i></span>
                                <input type="text" name="beneficiario" id="beneficiario" class="form-control"
                                    placeholder="Ex: Copel, Aluguel..." required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Valor do Boleto</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">R$</span>
                                <input type="text" id="campo_valor" name="valor" class="form-control" placeholder="0,00" required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Data de Vencimento</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-calendar-alt text-muted"></i></span>
                                <input type="date" name="data_vencimento" id="data_vencimento"
                                    class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 p-3 border rounded bg-light">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="repete_boleto" name="repetir">
                            <label class="form-check-label fw-bold" for="repete_boleto">
                                Repetir este lançamento (Parcelamento)
                            </label>
                        </div>

                        <div id="campos_repeticao" style="display: none;" class="mt-3 p-3 bg-white border rounded shadow-sm">
                            <div class="row align-items-end mb-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Qtd de Parcelas</label>
                                    <input type="number" name="parcelas" id="input_parcelas"
                                        class="form-control" value="2" min="2" max="72">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label small fw-bold">Intervalo entre parcelas</label>
                                    <select name="intervalo" id="input_intervalo" class="form-select">
                                        <option value="30" selected>Mensal (30 dias)</option>
                                        <option value="15">Quinzenal (15 dias)</option>
                                        <option value="7">Semanal (7 dias)</option>
                                        <option value="1">Diário (1 dia)</option>
                                    </select>
                                </div>
                                <div class="col-md-3 text-end">
                                    <small class="text-primary fw-bold d-block mb-2" id="info-parcela"></small>
                                </div>
                            </div>
                            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="mb-0 fw-bold text-muted">Conferência de Parcelas</h6>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="adicionarParcelaManual()">
                                        <i class="fas fa-plus me-1"></i> Adicionar Parcela Avulsa
                                    </button>
                                </div>
                                <table class="table table-sm table-hover border">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Parcela</th><th>Vencimento</th>
                                            <th>Valor (R$)</th><th class="text-center">Ação</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tabela_previa"></tbody>
                                </table>
                            </div>
                            <p class="small text-muted mt-2 mb-0">
                                <i class="fas fa-info-circle"></i>
                                As datas acima são previsões baseadas no intervalo escolhido.
                            </p>
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary px-4">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-5 shadow">
                            <i class="fas fa-check-circle me-1"></i> Salvar Boleto(s)
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/simple-mask-money@3.0.0/lib/simple-mask-money.min.js"></script>
<script>
const campoValor         = document.getElementById('campo_valor');
const inputParcelas      = document.getElementById('input_parcelas');
const inputIntervalo     = document.getElementById('input_intervalo');
const inputDataVenc      = document.getElementById('data_vencimento');
const infoParcela        = document.getElementById('info-parcela');
const checkboxRepetir    = document.getElementById('repete_boleto');
const divCamposRepeticao = document.getElementById('campos_repeticao');
const tabelaPrevia       = document.getElementById('tabela_previa');

const maskOpts = {
    prefix: '', fixed: true, fractionDigits: 2,
    decimalSeparator: ',', thousandsSeparator: '.', cursor: 'end'
};

function getValorFloat() {
    const raw = campoValor.value.trim();
    if (!raw) return 0;
    return parseFloat(raw.replace(/\./g, '').replace(',', '.')) || 0;
}

function floatToBr(v) {
    return v.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

// ─── Segmentos FEBRABAN ───────────────────────────────────────────────────────
const SEGMENTOS = {
    '1':'Prefeitura', '2':'Energia Elétrica / Gás', '3':'Telecomunicações',
    '4':'Multas de Trânsito', '5':'Água e Esgoto', '6':'IPTU / ISS',
    '7':'DPVAT', '8':'Uso Exclusivo Banco', '9':'Uso Exclusivo Empresa',
};

function detectarTipo(linha) {
    const t = linha.length;
    if (t === 47 && !linha.startsWith('8')) return '🏦 Boleto Bancário';
    if ((t === 47 || t === 48) && linha.startsWith('8'))
        return '📄 Convênio — ' + (SEGMENTOS[linha[1]] || 'Segmento ' + linha[1]);
    if (t === 44 && !linha.startsWith('8')) return '🏦 Boleto Bancário (cód. barras)';
    if (t === 44 &&  linha.startsWith('8')) return '📄 Convênio (cód. barras)';
    return `❓ Formato não reconhecido (${t} dígitos)`;
}

// ─── Monta codigo44 do convênio 48d ──────────────────────────────────────────
function convenio48paraCodigo44(l) {
    return l.substring(0, 11) + l.substring(12, 23)
         + l.substring(24, 35) + l.substring(36, 47);
}

// ─── Extração de dados ───────────────────────────────────────────────────────
//
// CONVÊNIO codigo44:
//   pos  0-1  = produto + segmento
//   pos  2    = tipo_valor
//   pos  3-4  = reservado (zeros)
//   pos  5-14 = VALOR (10d)
//   pos 15-43 = campo livre (29d):
//     [0:19]  = identificador banco/cedente (igual p/ todos os boletos do emissor)
//     [19:]   = UC / conta do cliente (diferencia contas do mesmo emissor)
//
//   conta_origem = produto(1) + segmento(1) + campo_livre[0:20] = 22d
//   → Garante que duas contas COMPESA gerem chaves DIFERENTES
//
// BANCÁRIO 47d:
//   valor     = últimos 10d
//   vencimento = fator + 07/10/1997
//   conta_origem = banco(3) + campo_livre[0:19] = 22d
// ─────────────────────────────────────────────────────────────────────────────
function extrairDados(linha) {
    const t = linha.length;
    let valor = 0, vencimento = null, conta = '', assinatura = '';

    if (t === 48 && linha.startsWith('8')) {
        const c44 = convenio48paraCodigo44(linha);
        valor     = parseFloat(c44.substring(5, 15)) / 100;
        const cl  = c44.substring(15);
        conta     = c44.substring(0, 2) + cl.substring(0, 20); // 22d
        assinatura = conta;
    }
    else if (t === 47 && linha.startsWith('8')) {
        const c44 = linha.substring(0, 11) + linha.substring(12, 23)
                  + linha.substring(24, 35) + linha.substring(36, 47);
        valor     = parseFloat(c44.substring(5, 15)) / 100;
        const cl  = c44.substring(15);
        conta     = c44.substring(0, 2) + cl.substring(0, 20);
        assinatura = conta;
    }
    else if (t === 47 && !linha.startsWith('8')) {
        valor = parseFloat(linha.slice(-10)) / 100;
        const fator = parseInt(linha.substring(33, 37));
        if (fator > 1000) {
            const base = new Date('1997-10-07T00:00:00');
            base.setDate(base.getDate() + fator);
            vencimento = base.toISOString().split('T')[0];
        }
        const cl   = linha.substring(4, 9) + linha.substring(11, 21) + linha.substring(22, 32);
        conta      = linha.substring(0, 3) + cl.substring(0, 19); // 22d
        assinatura = linha.substring(0, 3) + cl.substring(0, 18);
    }
    else if (t === 44) {
        if (linha.startsWith('8')) {
            valor  = parseFloat(linha.substring(5, 15)) / 100;
            conta  = linha.substring(0, 2) + linha.substring(15, 35); // 22d
        } else {
            valor  = parseFloat(linha.substring(34, 44)) / 100;
            const fator = parseInt(linha.substring(30, 34));
            if (fator > 1000) {
                const base = new Date('1997-10-07T00:00:00');
                base.setDate(base.getDate() + fator);
                vencimento = base.toISOString().split('T')[0];
            }
            conta = linha.substring(0, 3) + linha.substring(4, 23); // 22d
        }
        assinatura = conta;
    }

    return { valor, vencimento, conta, assinatura };
}

// ─── Função principal ─────────────────────────────────────────────────────────
let debounceTimer = null;

function decifrarBoleto(codigo) {
    if (!codigo) return;
    const linha = codigo.replace(/[^0-9]/g, '');
    if (linha.length < 10) return;

    document.getElementById('texto-tipo-boleto').textContent   = detectarTipo(linha);
    document.getElementById('badge-tipo-boleto').style.display = '';

    const { valor, vencimento, conta, assinatura } = extrairDados(linha);

    if (valor > 0) {
        campoValor.value = floatToBr(valor);
        campoValor.dispatchEvent(new Event('input'));
    }
    if (vencimento) {
        document.getElementById('data_vencimento').value = vencimento;
    }

    document.getElementById('assinatura_origem').value = assinatura;
    document.getElementById('conta_origem').value      = conta;

    clearTimeout(debounceTimer);
    if (conta || assinatura) {
        debounceTimer = setTimeout(() => buscarBeneficiario(conta, assinatura), 400);
    }
}

// ─── Busca beneficiário no histórico ─────────────────────────────────────────
async function buscarBeneficiario(conta, assinatura) {
    const inputBenef = document.getElementById('beneficiario');
    const spinner    = document.getElementById('spinner-beneficiario');
    const badge      = document.getElementById('badge-beneficiario-encontrado');
    const badgeNome  = document.getElementById('badge-nome-beneficiario');

    if (inputBenef.dataset.manuallyEdited === 'true') return;
    spinner.style.display = '';

    try {
        let nome = null;

        if (conta) {
            const r = await fetch(`/api/consultar-conta/${encodeURIComponent(conta)}`);
            const d = await r.json();
            if (d.sucesso && d.nome) nome = d.nome;
        }
        if (!nome && assinatura && assinatura !== conta) {
            const r = await fetch(`/api/consultar-beneficiario/${encodeURIComponent(assinatura)}`);
            const d = await r.json();
            if (d.sucesso && d.nome) nome = d.nome;
        }

        if (nome) {
            inputBenef.value    = nome;
            badgeNome.textContent = nome;
            badge.style.display   = '';
        } else {
            badge.style.display = 'none';
        }
    } catch (e) {
        console.error('Erro ao buscar beneficiário:', e);
    } finally {
        spinner.style.display = 'none';
    }
}

// ─── Parcelamento ─────────────────────────────────────────────────────────────
function atualizarInfoParcelas() {
    const v   = getValorFloat();
    const qtd = parseInt(inputParcelas.value) || 1;
    const int = parseInt(inputIntervalo.value) || 30;
    const dt  = inputDataVenc.value;
    tabelaPrevia.innerHTML = '';

    if (checkboxRepetir.checked && qtd > 1 && v > 0 && dt) {
        const base = new Date(dt + 'T00:00:00');
        for (let i = 0; i < qtd; i++) {
            const d = new Date(base);
            d.setDate(base.getDate() + i * int);
            tabelaPrevia.innerHTML += `<tr>
                <td class="align-middle text-nowrap">${i+1}ª Parcela</td>
                <td><input type="date" class="form-control form-control-sm"
                    name="vencimentos_parcelas[]" value="${d.toISOString().split('T')[0]}"></td>
                <td><div class="input-group input-group-sm">
                    <span class="input-group-text">R$</span>
                    <input type="text" class="form-control form-control-sm parcela-valor"
                        name="valores_parcelas[]" value="${floatToBr(v)}">
                </div></td>
                <td class="text-center"><button type="button" class="btn btn-sm btn-link text-danger"
                    onclick="this.closest('tr').remove();calcularTotalGeral()">
                    <i class="fas fa-trash"></i></button></td>
            </tr>`;
        }
        aplicarMascarasTabela(); calcularTotalGeral();
    } else { infoParcela.innerHTML = ''; }
}

function adicionarParcelaManual() {
    const n = tabelaPrevia.querySelectorAll('tr').length;
    const dts = tabelaPrevia.querySelectorAll('input[type="date"]');
    let dt = new Date();
    if (dts.length > 0) { dt = new Date(dts[dts.length-1].value+'T00:00:00'); dt.setDate(dt.getDate()+30); }
    tabelaPrevia.insertAdjacentHTML('beforeend', `<tr>
        <td class="align-middle text-nowrap">${n+1}ª Parcela (Extra)</td>
        <td><input type="date" class="form-control form-control-sm"
            name="vencimentos_parcelas[]" value="${dt.toISOString().split('T')[0]}"></td>
        <td><div class="input-group input-group-sm">
            <span class="input-group-text">R$</span>
            <input type="text" class="form-control form-control-sm parcela-valor"
                name="valores_parcelas[]" value="${floatToBr(getValorFloat())}">
        </div></td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-link text-danger"
            onclick="this.closest('tr').remove();calcularTotalGeral()">
            <i class="fas fa-trash"></i></button></td>
    </tr>`);
    aplicarMascarasTabela(); calcularTotalGeral();
}

function calcularTotalGeral() {
    let t = 0;
    document.querySelectorAll('.parcela-valor').forEach(e => {
        t += parseFloat(e.value.replace(/\./g,'').replace(',','.')) || 0;
    });
    if (t > 0) infoParcela.innerHTML = `Total Final: R$ ${floatToBr(t)}`;
}

function aplicarMascarasTabela() {
    if (typeof SimpleMaskMoney === 'undefined') return;
    document.querySelectorAll('.parcela-valor').forEach(e => SimpleMaskMoney.setMask(e, maskOpts));
}

// ─── Init ─────────────────────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', function () {
    if (typeof SimpleMaskMoney !== 'undefined') SimpleMaskMoney.setMask(campoValor, maskOpts);

    const b = document.getElementById('beneficiario');
    b.addEventListener('input',  function () { this.dataset.manuallyEdited = 'true'; });
    b.addEventListener('keyup',  function () {
        if (this.value === '') {
            this.dataset.manuallyEdited = 'false';
            document.getElementById('badge-beneficiario-encontrado').style.display = 'none';
        }
    });

    checkboxRepetir.addEventListener('change', function () {
        divCamposRepeticao.style.display = this.checked ? 'block' : 'none';
        if (this.checked && parseInt(inputParcelas.value) < 2) inputParcelas.value = 2;
        atualizarInfoParcelas();
    });

    tabelaPrevia.addEventListener('input', e => {
        if (e.target.classList.contains('parcela-valor')) calcularTotalGeral();
    });

    [campoValor, inputParcelas, inputIntervalo, inputDataVenc].forEach(el => {
        if (el) el.addEventListener('input', atualizarInfoParcelas);
    });
});
</script>
@endsection
