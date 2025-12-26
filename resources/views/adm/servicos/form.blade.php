@extends('adm.html_base')

@section('content')
<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">{{ $servico->exists ? 'Editar serviço' : 'Novo serviço' }}</h4>
            <p class="text-muted mb-0">
                Cada serviço precisa de um handler para informar ao backend para onde enviar os dados.
            </p>
        </div>
        <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.servicos.index') }}">
            ← Voltar para lista
        </a>
    </div>

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ $servico->exists ? route('admin.servicos.update', $servico) : route('admin.servicos.store') }}">
                @csrf
                @if($servico->exists)
                    @method('PUT')
                @endif

                <div class="row gy-3">
                    <div class="col-md-6">
                        <label class="form-label">Nome</label>
                        <input type="text" name="nome" class="form-control @error('nome') is-invalid @enderror"
                               value="{{ old('nome', $servico->nome) }}" required maxlength="120">
                        @error('nome')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control @error('slug') is-invalid @enderror"
                               value="{{ old('slug', $servico->slug) }}" required maxlength="120">
                        <small class="text-muted">Uso interno. Ex: manychat</small>
                        @error('slug')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Handler (classe)</label>
                        <input type="text" name="handler_class"
                               class="form-control @error('handler_class') is-invalid @enderror"
                               value="{{ old('handler_class', $servico->handler_class) }}" required maxlength="150">
                        <small class="text-muted">Ex: ManyChatService</small>
                        @error('handler_class')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Descrição (opcional)</label>
                        <input type="text" name="descricao"
                               class="form-control @error('descricao') is-invalid @enderror"
                               value="{{ old('descricao', $servico->descricao) }}" maxlength="255">
                        @error('descricao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-12">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="ativo"
                                   name="ativo" value="1"
                                   {{ old('ativo', $servico->ativo) ? 'checked' : '' }}>
                            <label class="form-check-label" for="ativo">
                                Serviço ativo
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        {{ $servico->exists ? 'Atualizar serviço' : 'Criar serviço' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
