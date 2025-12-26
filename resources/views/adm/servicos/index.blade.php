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
