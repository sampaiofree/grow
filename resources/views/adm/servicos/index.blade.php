@extends('adm.html_base')

@section('content')
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">Serviços</h4>
        <a class="btn btn-primary btn-sm" href="{{ route('admin.servicos.create') }}">Criar novo serviço</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">
            {{ session('success') }}
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Nome</th>
                            <th>Slug</th>
                            <th>Handler</th>
                            <th>Ativo</th>
                            <th>Atualizado em</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($servicos as $servico)
                            @php
                                $hasRelations = ($servico->campos_obrigatorios_count ?? 0) > 0
                                    || ($servico->webhook_endpoints_count ?? 0) > 0;
                            @endphp
                            <tr>
                                <td>{{ $servico->nome }}</td>
                                <td>{{ $servico->slug }}</td>
                                <td>{{ $servico->handler_class }}</td>
                                <td>
                                    @if($servico->ativo)
                                        <span class="badge bg-success">Sim</span>
                                    @else
                                        <span class="badge bg-secondary">Não</span>
                                    @endif
                                </td>
                                <td>{{ $servico->updated_at?->format('d/m/Y H:i') }}</td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-secondary me-1"
                                       href="{{ route('admin.servicos.campos.index', $servico) }}">
                                        Campos obrigatórios
                                    </a>
                                    <a class="btn btn-sm btn-outline-primary"
                                       href="{{ route('admin.servicos.edit', $servico) }}">
                                        Editar
                                    </a>
                                    @if($hasRelations)
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger ms-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deleteServicoModal-{{ $servico->id }}">
                                            Excluir
                                        </button>
                                        <div class="modal fade" id="deleteServicoModal-{{ $servico->id }}" tabindex="-1"
                                             aria-labelledby="deleteServicoModalLabel-{{ $servico->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title" id="deleteServicoModalLabel-{{ $servico->id }}">
                                                            Confirmar exclusao
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p class="mb-2">Este servico possui registros relacionados.</p>
                                                        <ul class="mb-0">
                                                            @if($servico->campos_obrigatorios_count > 0)
                                                                <li>{{ $servico->campos_obrigatorios_count }} campos obrigatorios</li>
                                                            @endif
                                                            @if($servico->webhook_endpoints_count > 0)
                                                                <li>{{ $servico->webhook_endpoints_count }} endpoints</li>
                                                            @endif
                                                        </ul>
                                                        <p class="mt-3 mb-0">Ao excluir, esses registros tambem serao removidos.</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                                                            Cancelar
                                                        </button>
                                                        <form method="POST"
                                                              action="{{ route('admin.servicos.destroy', $servico) }}"
                                                              class="d-inline">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-danger">Excluir servico</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <form method="POST"
                                              action="{{ route('admin.servicos.destroy', $servico) }}"
                                              class="d-inline ms-1">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted">
                                    Nenhum serviço cadastrado ainda.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
