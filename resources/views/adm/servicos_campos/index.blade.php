@extends('adm.html_base')

@section('content')
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">Campos obrigatórios do serviço "{{ $servico->nome }}"</h4>
            <p class="text-muted mb-0">
                O sistema usa esses campos para validar e preencher informações antes de disparar o handler.
            </p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.servicos.index') }}">
                ← Voltar para serviços
            </a>
            <a class="btn btn-primary btn-sm" href="{{ route('admin.servicos.campos.create', $servico) }}">
                Adicionar campo
            </a>
        </div>
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
                            <th>Nome exibido</th>
                            <th>Campo padrão</th>
                            <th>Tipo</th>
                            <th>Obrigatório</th>
                            <th class="text-end">Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($campos as $campo)
                            <tr>
                                <td>{{ $campo->nome_exibicao }}</td>
                                <td>{{ $campo->campo_padrao }}</td>
                                <td>{{ ucfirst($campo->tipo) }}</td>
                                <td>
                                    @if($campo->obrigatorio)
                                        <span class="badge bg-success">Sim</span>
                                    @else
                                        <span class="badge bg-secondary">Não</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a class="btn btn-sm btn-outline-primary"
                                       href="{{ route('admin.servicos.campos.edit', [$servico, $campo]) }}">
                                        Editar
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">
                                    Nenhum campo definido ainda.
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
