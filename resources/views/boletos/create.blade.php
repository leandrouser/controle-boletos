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
                            <small class="text-primary mt-1 d-block" id="info-parcela" style="font-weight: 600;"></small>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Data de Vencimento</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white"><i class="fas fa-calendar-alt text-muted"></i></span>
                                <input type="date" name="data_vencimento" class="form-control" required>
                            </div>
                        </div>
                    </div>

                    <div class="mt-3 p-3 border rounded bg-light">
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" id="repete_boleto" name="repetir">
                            <label class="form-check-label fw-bold" for="repete_boleto">Repetir este lançamento (Parcelamento)</label>
                        </div>
                        
                        <div id="campos_repeticao" style="display: none;">
                            <div class="row align-items-end">
                                <div class="col-md-6">
                                    <label class="form-label small fw-bold">Quantidade de Parcelas</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-layer-group text-muted"></i></span>
                                        <input type="number" name="parcelas" id="input_parcelas" class="form-control" value="1" min="1" max="72">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <p class="small text-muted mb-2">
                                        <i class="fas fa-info-circle"></i> Os boletos serão criados com intervalo de 30 dias.
                                    </p>
                                </div>
                            </div>
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

<script>
// 1. Definições de Elementos Globais
const campoValor = document.getElementById('campo_valor');
const inputParcelas = document.getElementById('input_parcelas');
const infoParcela = document.getElementById('info-parcela');
const checkboxRepetir = document.getElementById('repete_boleto');
const divCamposRepeticao = document.getElementById('campos_repeticao');

// 2. Função de Decifrar Boleto
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
            document.getElementsByName('data_vencimento')[0].value = dataBase.toISOString().split('T')[0];
        }
    }
}

// 3. Função de Cálculo de Parcelas
function atualizarInfoParcelas() {
    const valorUnitario = SimpleMaskMoney.getRawValue(campoValor);
    const qtd = parseInt(inputParcelas.value) || 1;
    
    if (checkboxRepetir.checked && qtd > 1 && valorUnitario > 0) {
        const total = (valorUnitario * qtd).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
        infoParcela.innerHTML = `<i class="fas fa-calculator"></i> Total acumulado: <strong>${total}</strong>`;
    } else {
        infoParcela.innerHTML = '';
    }
}

// 4. Inicialização do DOM
document.addEventListener("DOMContentLoaded", function() {
    // Foco inicial
    document.getElementById('codigo_barras').focus();

    // Configuração Máscara Dinheiro
    SimpleMaskMoney.setMask(campoValor, {
        prefix: '', fixed: true, fractionDigits: 2,
        decimalSeparator: ',', thousandsSeparator: '.', cursor: 'end'
    });

    // Lógica para Mostrar/Esconder Parcelas
    checkboxRepetir.addEventListener('change', function() {
        if (this.checked) {
            divCamposRepeticao.style.display = 'block';
            if(inputParcelas.value <= 1) inputParcelas.value = 2;
        } else {
            divCamposRepeticao.style.display = 'none';
        }
        atualizarInfoParcelas();
    });

    // Ouvintes para o cálculo
    inputParcelas.addEventListener('input', atualizarInfoParcelas);
    campoValor.addEventListener('input', atualizarInfoParcelas);

    // Tratamento de Envio do Formulário
    document.getElementById('form-boleto').addEventListener('submit', function() {
        let valorPuro = SimpleMaskMoney.getRawValue(campoValor);
        campoValor.value = valorPuro;
    });

    // Lógica do Alerta de Sucesso
    const alerta = document.getElementById('alerta-sucesso');
    if (alerta) {
        setTimeout(() => {
            alerta.style.transition = "opacity 0.5s ease";
            alerta.style.opacity = "0";
            setTimeout(() => alerta.remove(), 500);
        }, 3000);
    }
});
</script>
@endsection