@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card shadow-lg border-0">
            <div class="card-header bg-primary text-white p-3">
                <h4 class="mb-0"><i class="fas fa-edit me-2"></i> Editar Boleto</h4>
                <small>Atualize as informações do título abaixo</small>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('boletos.update', $boleto->id) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label fw-bold">Beneficiário</label>
                        <input type="text" name="beneficiario" class="form-control" value="{{ $boleto->beneficiario }}" required>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Valor</label>
                            <div class="input-group">
                                <span class="input-group-text bg-white">R$</span>
                                <input type="number" step="0.01" name="valor" class="form-control" value="{{ $boleto->valor }}" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Data de Vencimento</label>
                            <input type="date" name="data_vencimento" class="form-control" 
                                value="{{ \Carbon\Carbon::parse($boleto->data_vencimento)->format('Y-m-d') }}" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Código de Barras (Opcional)</label>
                        <textarea name="codigo_barras" class="form-control" rows="3" placeholder="Linha digitável ou código de barras...">{{ $boleto->codigo_barras }}</textarea>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold">Status do Pagamento</label>
                        <select name="status" class="form-select">
                            <option value="pendente" {{ $boleto->status == 'pendente' ? 'selected' : '' }}>Pendente</option>
                            <option value="pago" {{ $boleto->status == 'pago' ? 'selected' : '' }}>Pago</option>
                        </select>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary px-4">
                            <i class="fas fa-times me-1"></i> Cancelar
                        </a>
                        <button type="submit" class="btn btn-primary px-5 shadow">
                            <i class="fas fa-save me-1"></i> Salvar Alterações
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection