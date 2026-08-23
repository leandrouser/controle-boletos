@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-0"><i class="fas fa-tags me-2"></i> Categorias</h4>
        <p class="text-muted mb-0 small">Gerencie as categorias usadas para classificar os boletos.</p>
    </div>
    <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> Voltar
    </a>
</div>

{{-- Formulário de nova categoria --}}
<div class="card shadow-sm border-0 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('categorias.store') }}" method="POST" class="row g-2 align-items-end">
            @csrf
            <div class="col-md-8">
                <label class="form-label small fw-bold text-muted">Nova Categoria</label>
                <input type="text" name="nome" class="form-control" placeholder="Ex: Assinaturas, Manutenção..."
                    value="{{ old('nome') }}" required maxlength="255">
                @error('nome')
                    <div class="text-danger small mt-1">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-primary w-100 fw-bold">
                    <i class="fas fa-plus me-1"></i> Adicionar
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Listagem --}}
<div class="card shadow-sm border-0">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Categoria</th>
                    <th class="text-center">Boletos vinculados</th>
                    <th class="text-center" style="width:160px;">Ações</th>
                </tr>
            </thead>
            <tbody>
                @forelse($categorias as $categoria)
                    <tr>
                        <td class="fw-bold">{{ $categoria->nome }}</td>
                        <td class="text-center">
                            <span class="badge bg-secondary-subtle text-secondary border">
                                {{ $categoria->boletos_count }}
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group shadow-sm">
                                <button type="button" class="btn btn-sm btn-white text-primary"
                                    data-bs-toggle="modal" data-bs-target="#modal-editar-{{ $categoria->id }}"
                                    title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-white text-danger"
                                    onclick="confirmarExclusao({{ $categoria->id }}, '{{ addslashes($categoria->nome) }}', {{ $categoria->boletos_count }})"
                                    title="Excluir">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- Modal de edição --}}
                    <div class="modal fade" id="modal-editar-{{ $categoria->id }}" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered">
                            <div class="modal-content">
                                <form action="{{ route('categorias.update', $categoria->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header">
                                        <h5 class="modal-title"><i class="fas fa-edit me-2"></i> Editar Categoria</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <label class="form-label fw-bold">Nome</label>
                                        <input type="text" name="nome" class="form-control"
                                            value="{{ $categoria->nome }}" required maxlength="255">
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary fw-bold">Salvar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="3" class="text-center py-5 text-muted">
                            Nenhuma categoria cadastrada ainda.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Formulários de exclusão, fora da tabela --}}
@foreach($categorias as $categoria)
    <form id="form-excluir-{{ $categoria->id }}"
        action="{{ route('categorias.destroy', $categoria->id) }}"
        method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
@endforeach

<script>
    function confirmarExclusao(id, nome, qtdBoletos) {
        if (qtdBoletos > 0) {
            alert(`Não é possível excluir "${nome}": há ${qtdBoletos} boleto(s) usando essa categoria.\n\nEdite esses boletos para outra categoria antes de excluir.`);
            return;
        }
        if (confirm(`Excluir a categoria "${nome}"?`)) {
            document.getElementById('form-excluir-' + id).submit();
        }
    }
</script>

@endsection
