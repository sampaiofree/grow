@extends('adm.html_base')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-3">Novo Endpoint</h4>
                    <form method="POST" action="{{ route('webhooks.store') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Nome</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Ex: Lead ManyChat" required>
                        </div>
                        <div class="mb-3">
                            <label for="servico_id" class="form-label">Serviço</label>
                            <select class="form-select" id="servico_id" name="servico_id" required>
                                <option value="">Selecione um serviço</option>
                                @foreach($servicos as $servico)
                                    <option value="{{ $servico->id }}" {{ old('servico_id') == $servico->id ? 'selected' : '' }}>
                                        {{ $servico->nome }}
                                    </option>
                                @endforeach
                            </select>
                            @error('servico_id')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                            <label class="form-check-label" for="is_active">Ativo</label>
                        </div>
                        <button type="submit" class="btn btn-primary">Criar endpoint</button>
                    </form>
                    @if($servicos->isEmpty())
                        <div class="alert alert-warning mt-3 mb-0">
                            Nenhum serviço disponível. @if(auth()->user()?->is_admin)
                                <a href="{{ route('admin.servicos.index') }}" class="alert-link">Cadastre um serviço</a> antes de criar endpoints.
                            @else
                                Peça para um administrador cadastrar um serviço disponível.
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title mb-3">Meus Endpoints</h4>
                    @if($endpoints->isEmpty())
                        <p class="text-muted mb-0">Nenhum endpoint criado.</p>
                    @else
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Serviço</th>
                                    <th>URL pública</th>
                                    <th>Status</th>
                                    <th style="width: 180px;">Ações</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($endpoints as $endpoint)
                                        @php
                                            $publicUrl = rtrim(config('app.url'), '/').'/api/webhook/'.$endpoint->uuid;
                                        @endphp
                                        <tr>
                                            <td class="fw-semibold">{{ $endpoint->name }}</td>
                                            <td>{{ $endpoint->servico?->nome ?? '—' }}</td>
                                            <td>
                                                <small class="text-muted d-block">UUID: {{ $endpoint->uuid }}</small>
                                                <div class="input-group input-group-sm">
                                                    <input type="text" class="form-control" value="{{ $publicUrl }}" readonly>
                                                    <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText('{{ $publicUrl }}')">Copiar</button>
                                                </div>
                                            </td>
                                            <td>
                                                @if($endpoint->is_active)
                                                    <span class="badge bg-success">Ativo</span>
                                                @else
                                                    <span class="badge bg-secondary">Inativo</span>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('webhooks.show', $endpoint) }}" class="btn btn-sm btn-outline-primary me-1">Editar</a>
                                                <form method="POST" action="{{ route('webhooks.destroy', $endpoint) }}" class="d-inline-block">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger">Excluir</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
