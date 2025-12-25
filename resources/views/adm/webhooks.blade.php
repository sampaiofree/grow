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
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" checked>
                            <label class="form-check-label" for="is_active">Ativo</label>
                        </div>
                        <button type="submit" class="btn btn-primary">Criar endpoint</button>
                    </form>
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
                                                <a href="{{ route('webhooks.show', $endpoint) }}" class="btn btn-sm btn-outline-primary">Editar</a>
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
