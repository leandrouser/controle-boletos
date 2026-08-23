@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0"><i class="fas fa-trash-alt me-2"></i> Lixeira</h4>
        <p class="text-muted mb-0 small">Boletos excluídos. Você pode restaurá-los ou excluir definitivamente.</p>
    </div>
    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Voltar
    </a>
</div>

<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Beneficiário</th>
                    <th>Categoria</th>
                    <th>Valor</th>
                    <th>Vencimento</th>
                    <th>Excluído em</th>
                    <th class="text-center">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($boletos as $boleto)
                    <tr>
                        <td class="fw-bold">{{ $boleto->beneficiario }}</td>
                        <td>
                            <span class="badge bg-secondary-subtle text-secondary border">
                                {{ $boleto->categoria_label }}
                            </span>
                        </td>
                        <td>R$ {{ number_format($boleto->valor, 2, ',', '.') }}</td>
                        <td>{{ $boleto->data_vencimento->format('d/m/Y') }}</td>
                        <td class="text-muted small">{{ $boleto->deleted_at->format('d/m/Y H:i') }}</td>
                        <td class="text-center">
                            <div class="btn-group shadow-sm">
                                <form action="{{ route('boletos.restaurar', $boleto->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-success" title="Restaurar">
                                        <i class="fas fa-rotate-left"></i> Restaurar
                                    </button>
                                </form>
                                <button type="button" class="btn btn-sm btn-outline-danger"
                                    onclick="excluirDefinitivo({{ $boleto->id }}, '{{ addslashes($boleto->beneficiario) }}')"
                                    title="Excluir definitivamente">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="fas fa-check-circle fa-2x mb-2 d-block text-success opacity-50"></i>
                            A lixeira está vazia.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($boletos->hasPages())
        <div class="card-footer bg-white py-3 border-top">
            <div class="d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    Mostrando <b>{{ $boletos->firstItem() ?? 0 }}</b> de <b>{{ $boletos->total() }}</b>
                </small>
                <div>{{ $boletos->links() }}</div>
            </div>
        </div>
    @endif
</div>

{{-- Formulários de exclusão definitiva, fora da tabela --}}
@foreach($boletos as $boleto)
    <form id="form-excluir-definitivo-{{ $boleto->id }}"
        action="{{ route('boletos.excluirDefinitivo', $boleto->id) }}"
        method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
@endforeach

<script>
    function excluirDefinitivo(id, nome) {
        if (confirm(`ATENÇÃO: excluir "${nome}" definitivamente não pode ser desfeito. Continuar?`)) {
            document.getElementById('form-excluir-definitivo-' + id).submit();
        }
    }
</script>

@endsection
