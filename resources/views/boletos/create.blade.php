@extends('layouts.app')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/simple-mask-money@3.0.0/lib/simple-mask-money.min.js"></script>

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-primary text-white p-3">
                <h4 class="mb-0"><i class="fas fa-barcode me-2"></i> Novo Boleto</h4>
                <small>Preencha os dados ou utilize o leitor no código de barras</small>
            </div>
            <div class="card-body p-4">
                @if(session('success'))
                    <div id="alerta-sucesso" class="alert alert-success border-0 shadow-sm d-flex align-items-center" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

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
                                <input type="text" name="beneficiario" class="form-control" placeholder="Ex: Copel, Aluguel, Cartão..." required>
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
                            <label class="form-label fw-bold">Data de Vencimento (1ª Parcela)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-calendar-alt text-muted"></i></span>
                                <input type="date" name="data_vencimento" id="data_vencimento" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 p-3 border rounded bg-light">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="repete_boleto" name="repetir">
                            <label class="form-check-label fw-bold" for="repete_boleto">Repetir este lançamento (Parcelamento)</label>
                        </div>
                        
                        <div id="campos_repeticao" style="display: none;" class="mt-3 p-3 bg-white border rounded shadow-sm">
                            <div class="row align-items-end mb-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Qtd de Parcelas</label>
                                    <input type="number" name="parcelas" id="input_parcelas" class="form-control" value="1" min="1" max="72">
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
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="adicionarParcelaManual()">
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
                                <i class="fas fa-info-circle"></i> As datas acima são previsões baseadas no intervalo escolhido.
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
// Elementos Globais
const campoValor = document.getElementById('campo_valor');
const inputParcelas = document.getElementById('input_parcelas');
const inputIntervalo = document.getElementById('input_intervalo');
const inputDataVenc = document.getElementById('data_vencimento');
const infoParcela = document.getElementById('info-parcela');
const checkboxRepetir = document.getElementById('repete_boleto');
const divCamposRepeticao = document.getElementById('campos_repeticao');
const tabelaPrevia = document.getElementById('tabela_previa');

// 1. Pega valor limpo para o banco (ex: 1.250,00 -> 1250.00)
function getValorLimpo() {
    if (typeof SimpleMaskMoney !== 'undefined') {
        return SimpleMaskMoney.formatToNumber(campoValor.value);
    }
    let v = campoValor.value.replace(/\./g, '').replace(',', '.');
    return parseFloat(v) || 0;
}

// 2. Decifra código de barras
function decifrarBoleto(codigo) {
    if (!codigo) return;
    codigo = codigo.replace(/[^0-9]/g, '');
    if (codigo.length >= 44) {
        let valorStr = codigo.substr(codigo.length - 10);
        let valorFinal = (parseFloat(valorStr) / 100).toFixed(2);
        campoValor.value = valorFinal.replace('.', ',');
        campoValor.dispatchEvent(new Event('input')); 

        let fatorPos = (codigo.length === 44) ? 5 : 33;
        let fatorVencimento = parseInt(codigo.substr(fatorPos, 4));
        if (fatorVencimento > 1000) {
            let dataBase = new Date('1997-10-07T00:00:00');
            dataBase.setDate(dataBase.getDate() + fatorVencimento + 1);
            if(inputDataVenc) {
                inputDataVenc.value = dataBase.toISOString().split('T')[0];
                inputDataVenc.dispatchEvent(new Event('input'));
            }
        }
    }
}

// 3. Gera a tabela automática
function atualizarInfoParcelas() {
    const valorUnitario = getValorLimpo();
    const qtd = parseInt(inputParcelas.value) || 1;
    const intervalo = parseInt(inputIntervalo.value) || 30;
    const dataInicialStr = inputDataVenc.value;

    if (!tabelaPrevia) return;
    tabelaPrevia.innerHTML = '';

    if (checkboxRepetir.checked && qtd > 1 && valorUnitario > 0 && dataInicialStr) {
        let dataBase = new Date(dataInicialStr + 'T00:00:00');
        
        for (let i = 0; i < qtd; i++) {
            let novaData = new Date(dataBase);
            novaData.setDate(dataBase.getDate() + (i * intervalo));
            let dataInput = novaData.toISOString().split('T')[0];
            let valorInput = valorUnitario.toFixed(2).replace('.', ',');

            let row = `<tr>
                <td class="align-middle text-nowrap">${i + 1}ª Parcela</td>
                <td><input type="date" class="form-control form-control-sm" name="vencimentos_parcelas[]" value="${dataInput}"></td>
                <td>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text">R$</span>
                        <input type="text" class="form-control form-control-sm mask-dinheiro-parcela" name="valores_parcelas[]" value="${valorInput}">
                    </div>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-link text-danger" onclick="this.closest('tr').remove(); calcularTotalGeral();">
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

// 4. Adiciona parcela avulsa manual
function adicionarParcelaManual() {
    const qtdAtual = tabelaPrevia.querySelectorAll('tr').length;
    const valorUnitario = getValorLimpo();
    
    let dataSugerida = new Date();
    const ultimasDatas = tabelaPrevia.querySelectorAll('input[type="date"]');
    if (ultimasDatas.length > 0) {
        dataSugerida = new Date(ultimasDatas[ultimasDatas.length - 1].value + 'T00:00:00');
        dataSugerida.setDate(dataSugerida.getDate() + 30);
    }

    const dataInput = dataSugerida.toISOString().split('T')[0];
    const valorInput = valorUnitario.toFixed(2).replace('.', ',');

    const row = `<tr>
        <td class="align-middle text-nowrap">${qtdAtual + 1}ª Parcela (Extra)</td>
        <td><input type="date" class="form-control form-control-sm" name="vencimentos_parcelas[]" value="${dataInput}"></td>
        <td>
            <div class="input-group input-group-sm">
                <span class="input-group-text">R$</span>
                <input type="text" class="form-control form-control-sm mask-dinheiro-parcela" name="valores_parcelas[]" value="${valorInput}">
            </div>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-link text-danger" onclick="this.closest('tr').remove(); calcularTotalGeral();">
                <i class="fas fa-trash"></i>
            </button>
        </td>
    </tr>`;
    
    tabelaPrevia.insertAdjacentHTML('beforeend', row);
    aplicarMascarasTabela();
    calcularTotalGeral();
}

// 5. Calcula o total somando todos os inputs da tabela
function calcularTotalGeral() {
    let total = 0;
    document.querySelectorAll('.mask-dinheiro-parcela').forEach(el => {
        total += (typeof SimpleMaskMoney !== 'undefined') 
            ? SimpleMaskMoney.formatToNumber(el.value) 
            : (parseFloat(el.value.replace(',', '.')) || 0);
    });
    
    if (total > 0) {
        infoParcela.innerHTML = `Total Final: ${total.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' })}`;
    }
}

// 6. Helper para máscaras
function aplicarMascarasTabela() {
    document.querySelectorAll('.mask-dinheiro-parcela').forEach(el => {
        if (typeof SimpleMaskMoney !== 'undefined') {
            SimpleMaskMoney.setMask(el, {
                prefix: '', fixed: true, fractionDigits: 2,
                decimalSeparator: ',', thousandsSeparator: '.', cursor: 'end'
            });
        }
    });
}

// Inicialização
document.addEventListener("DOMContentLoaded", function() {
    if (typeof SimpleMaskMoney !== 'undefined') {
        SimpleMaskMoney.setMask(campoValor, {
            prefix: '', fixed: true, fractionDigits: 2,
            decimalSeparator: ',', thousandsSeparator: '.', cursor: 'end'
        });
    }

    if(checkboxRepetir) {
        checkboxRepetir.addEventListener('change', function() {
            divCamposRepeticao.style.display = this.checked ? 'block' : 'none';
            if(this.checked && inputParcelas.value <= 1) inputParcelas.value = 2;
            atualizarInfoParcelas();
        });
    }

    tabelaPrevia.addEventListener('input', function(e) {
        if (e.target.classList.contains('mask-dinheiro-parcela')) {
            calcularTotalGeral();
        }
    });

    [campoValor, inputParcelas, inputIntervalo, inputDataVenc].forEach(el => {
        if(el) el.addEventListener('input', atualizarInfoParcelas);
    });

    document.getElementById('form-boleto').addEventListener('submit', function(e) {
        campoValor.value = getValorLimpo();
        document.querySelectorAll('.mask-dinheiro-parcela').forEach(el => {
            el.value = (typeof SimpleMaskMoney !== 'undefined') 
                ? SimpleMaskMoney.formatToNumber(el.value) 
                : el.value.replace(/\./g, '').replace(',', '.');
        });
    });
});
</script>
@endsection