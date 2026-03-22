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

                <input type="hidden" name="assinatura_origem" id="assinatura_origem">

                <form action="{{ route('boletos.store') }}" method="POST" id="form-boleto">
                    @csrf

                    <div class="mb-4 p-3 bg-light border-start border-primary border-4 rounded">
                        <label class="form-label fw-bold text-primary">
                            <i class="fas fa-expand me-1"></i> Linha Digitável / Código de Barras
                        </label>
                        <input type="text" id="codigo_barras" name="codigo_barras"
                            class="form-control form-control-lg shadow-sm"
                            placeholder="Cole o código aqui para preenchimento automático..."
                            oninput="decifrarBoleto(this.value)" autofocus>
                    </div>

                    <hr class="text-muted mb-4">

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Beneficiário / Empresa</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-building text-muted"></i></span>
                                <input type="text" name="beneficiario" class="form-control"
                                    placeholder="Ex: Copel, Aluguel, Cartão..."
                                    value="{{ old('beneficiario') }}" required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Valor do Boleto</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">R$</span>
                            
                                <input type="text" id="campo_valor" name="valor"
                                    class="form-control" placeholder="0,00"
                                    inputmode="decimal" required>
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
                                            <th>Parcela</th>
                                            <th>Vencimento</th>
                                            <th>Valor (R$)</th>
                                            <th class="text-center">Ação</th>
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
const campoValor     = document.getElementById('campo_valor');
const inputParcelas  = document.getElementById('input_parcelas');
const inputIntervalo = document.getElementById('input_intervalo');
const inputDataVenc  = document.getElementById('data_vencimento');
const infoParcela    = document.getElementById('info-parcela');
const checkboxRepetir    = document.getElementById('repete_boleto');
const divCamposRepeticao = document.getElementById('campos_repeticao');
const tabelaPrevia       = document.getElementById('tabela_previa');

const maskOpts = {
    prefix: '',
    fixed: true,
    fractionDigits: 2,
    decimalSeparator: ',',
    thousandsSeparator: '.',
    cursor: 'end'
};

function getValorFloat() {
    const raw = campoValor.value.trim();
    if (!raw) return 0;
    const en = raw.replace(/\./g, '').replace(',', '.');
    return parseFloat(en) || 0;
}

function floatToBr(valor) {
    return valor.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function decifrarBoleto(codigo) {
    if (!codigo) return;

    let linha = codigo.replace(/[^0-9]/g, '');
    let valor = 0;
    let vencimento = null;
    let assinatura = '';

    if (linha.length >= 44) {
        verificarDuplicado(linha);
    }

    if (linha.length === 47) {
        let valorStr = linha.slice(-10);
        valor = parseFloat(valorStr) / 100;

        let fatorVencimento = linha.slice(33, 37);
        if (parseInt(fatorVencimento) > 1000) {
            let dataBase = new Date('1997-10-07T00:00:00');
            dataBase.setDate(dataBase.getDate() + parseInt(fatorVencimento));
            vencimento = dataBase.toISOString().split('T')[0];
        }

        assinatura = linha.substring(0, 4) + linha.substring(4, 19);
    }
    else if (linha.length === 48) {
        let linhaLimpa = linha.substring(0, 11) +
                         linha.substring(12, 23) +
                         linha.substring(24, 35) +
                         linha.substring(36, 47);

        let valorStr = linhaLimpa.substring(4, 15);
        valor = parseFloat(valorStr) / 100;
        assinatura = linhaLimpa.substring(0, 15);

        let dataStr = linhaLimpa.substring(19, 27);
        if (dataStr.match(/^20[2-9][0-9][0-1][0-9][0-3][0-9]$/)) {
            vencimento = `${dataStr.substring(0, 4)}-${dataStr.substring(4, 6)}-${dataStr.substring(6, 8)}`;
        }
    }

    if (assinatura) {
        document.getElementById('assinatura_origem').value = assinatura;
        fetch(`/api/consultar-beneficiario/${assinatura}`)
            .then(res => res.json())
            .then(data => {
                if (data.sucesso && document.getElementsByName('beneficiario')[0].value === '') {
                    document.getElementsByName('beneficiario')[0].value = data.nome;
                }
            });
    }

    if (valor > 0) {
        campoValor.value = floatToBr(valor);
        campoValor.dispatchEvent(new Event('input'));
    }

    if (vencimento) {
        inputDataVenc.value = vencimento;
        inputDataVenc.dispatchEvent(new Event('input'));
    }
}

function atualizarInfoParcelas() {
    const valorUnitario  = getValorFloat();
    const qtd            = parseInt(inputParcelas.value) || 1;
    const intervalo      = parseInt(inputIntervalo.value) || 30;
    const dataInicialStr = inputDataVenc.value;

    if (!tabelaPrevia) return;
    tabelaPrevia.innerHTML = '';

    if (checkboxRepetir.checked && qtd > 1 && valorUnitario > 0 && dataInicialStr) {
        let dataBase = new Date(dataInicialStr + 'T00:00:00');

        for (let i = 0; i < qtd; i++) {
            let novaData = new Date(dataBase);
            novaData.setDate(dataBase.getDate() + (i * intervalo));
            let dataInput  = novaData.toISOString().split('T')[0];
            let valorInput = floatToBr(valorUnitario);

            let row = `<tr>
                <td class="align-middle text-nowrap">${i + 1}ª Parcela</td>
                <td><input type="date" class="form-control form-control-sm"
                    name="vencimentos_parcelas[]" value="${dataInput}"></td>
                <td>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">R$</span>
                        <input type="text" class="form-control form-control-sm parcela-valor"
                            name="valores_parcelas[]" value="${valorInput}">
                    </div>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-link text-danger"
                        onclick="this.closest('tr').remove(); calcularTotalGeral();">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>`;
            tabelaPrevia.innerHTML += row;
        }

        aplicarMascarasTabela();
        calcularTotalGeral();
    } else {
        infoParcela.innerHTML = '';
    }
}

function adicionarParcelaManual() {
    const qtdAtual      = tabelaPrevia.querySelectorAll('tr').length;
    const valorUnitario = getValorFloat();

    let dataSugerida = new Date();
    const ultimasDatas = tabelaPrevia.querySelectorAll('input[type="date"]');
    if (ultimasDatas.length > 0) {
        dataSugerida = new Date(ultimasDatas[ultimasDatas.length - 1].value + 'T00:00:00');
        dataSugerida.setDate(dataSugerida.getDate() + 30);
    }

    const dataInput  = dataSugerida.toISOString().split('T')[0];
    const valorInput = floatToBr(valorUnitario);

    const row = `<tr>
        <td class="align-middle text-nowrap">${qtdAtual + 1}ª Parcela (Extra)</td>
        <td><input type="date" class="form-control form-control-sm"
            name="vencimentos_parcelas[]" value="${dataInput}"></td>
        <td>
            <div class="input-group input-group-sm">
                <span class="input-group-text">R$</span>
                <input type="text" class="form-control form-control-sm parcela-valor"
                    name="valores_parcelas[]" value="${valorInput}">
            </div>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-link text-danger"
                onclick="this.closest('tr').remove(); calcularTotalGeral();">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>`;

    tabelaPrevia.insertAdjacentHTML('beforeend', row);
    aplicarMascarasTabela();
    calcularTotalGeral();
}

function calcularTotalGeral() {
    let total = 0;
    document.querySelectorAll('.parcela-valor').forEach(el => {
        const en = el.value.replace(/\./g, '').replace(',', '.');
        total += parseFloat(en) || 0;
    });

    if (total > 0) {
        infoParcela.innerHTML = `Total Final: ${floatToBr(total).replace(/^/, 'R$ ')}`;
    }
}

function aplicarMascarasTabela() {
    document.querySelectorAll('.parcela-valor').forEach(el => {
        if (typeof SimpleMaskMoney !== 'undefined') {
            SimpleMaskMoney.setMask(el, maskOpts);
        }
    });
}

async function verificarDuplicado(codigo) {
    if (codigo.length < 44) return;
    try {
        const response = await fetch(`/api/verificar-boleto-duplicado?codigo=${codigo}`);
        const data = await response.json();

        if (data.duplicado) {
            alert(`⚠️ Atenção: Este boleto já foi cadastrado para o beneficiário: ${data.beneficiario} em ${data.data_cadastro}`);
            document.getElementById('codigo_barras').classList.add('is-invalid');
        } else {
            document.getElementById('codigo_barras').classList.remove('is-invalid');
        }
    } catch (e) {
        console.error("Erro ao validar duplicidade");
    }
}

document.addEventListener('DOMContentLoaded', function () {

    if (typeof SimpleMaskMoney !== 'undefined') {
        SimpleMaskMoney.setMask(campoValor, maskOpts);
    }

    checkboxRepetir.addEventListener('change', function () {
        divCamposRepeticao.style.display = this.checked ? 'block' : 'none';
        if (this.checked && parseInt(inputParcelas.value) < 2) inputParcelas.value = 2;
        atualizarInfoParcelas();
    });

    tabelaPrevia.addEventListener('input', function (e) {
        if (e.target.classList.contains('parcela-valor')) {
            calcularTotalGeral();
        }
    });

    [campoValor, inputParcelas, inputIntervalo, inputDataVenc].forEach(el => {
        if (el) el.addEventListener('input', atualizarInfoParcelas);
    });

    document.getElementById('form-boleto').addEventListener('submit', function () {
    });
});
</script>
@endsection
