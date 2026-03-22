@extends('layouts.app')

@section('content')

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-warning text-dark p-3">
                <h4 class="mb-0"><i class="fas fa-edit me-2"></i> Editar Boleto</h4>
                <small>Atualize os dados do boleto selecionado</small>
            </div>
            <div class="card-body p-4">

                @if(session('success'))
                    <div class="alert alert-success border-0 shadow-sm d-flex align-items-center" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger border-0 shadow-sm">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form action="{{ route('boletos.update', $boleto->id) }}" method="POST" id="form-editar">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-12 mb-3">
                            <label class="form-label fw-bold">Beneficiário / Empresa</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fas fa-building text-muted"></i>
                                </span>
                                <input type="text" name="beneficiario" class="form-control"
                                    value="{{ old('beneficiario', $boleto->beneficiario) }}"
                                    placeholder="Ex: Copel, Aluguel, Cartão..." required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Valor do Boleto</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">R$</span>
                            
                                <input type="text" id="campo_valor" name="valor"
                                    class="form-control" inputmode="decimal"
                                    value="{{ number_format($boleto->valor, 2, ',', '.') }}"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Data de Vencimento</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">
                                    <i class="fas fa-calendar-alt text-muted"></i>
                                </span>
                                <input type="date" name="data_vencimento" class="form-control"
                                    value="{{ old('data_vencimento', $boleto->data_vencimento) }}"
                                    required>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Status</label>
                            <select name="status" class="form-select">
                                <option value="pendente" {{ $boleto->status == 'pendente' ? 'selected' : '' }}>
                                    Pendente
                                </option>
                                <option value="pago" {{ $boleto->status == 'pago' ? 'selected' : '' }}>
                                    Pago
                                </option>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Código de Barras</label>
                            <input type="text" name="codigo_barras" class="form-control"
                                value="{{ old('codigo_barras', $boleto->codigo_barras) }}"
                                placeholder="Opcional">
                        </div>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-3">
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary px-4">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-warning px-5 shadow fw-bold">
                            <i class="fas fa-save me-1"></i> Salvar Alterações
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

<script src="https://unpkg.com/simple-mask-money@3.0.0/lib/simple-mask-money.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const campoValor = document.getElementById('campo_valor');

    if (typeof SimpleMaskMoney !== 'undefined' && campoValor) {
        SimpleMaskMoney.setMask(campoValor, {
            prefix: '',
            fixed: true,
            fractionDigits: 2,
            decimalSeparator: ',',
            thousandsSeparator: '.',
            cursor: 'end'
        });
    }

});
</script>
@endsection
