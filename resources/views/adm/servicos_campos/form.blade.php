@extends('adm.html_base')

@section('content')
@php
    $tipos = [
        'string' => 'Texto livre',
        'email' => 'E-mail',
        'phone' => 'Telefone/celular',
        'number' => 'Número',
        'boolean' => 'Booleano',
        'url' => 'URL',
    ];
@endphp

<div class="container-fluid mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">
                {{ $campo->exists ? 'Editar campo obrigatório' : 'Novo campo obrigatório' }}
            </h4>
            <p class="text-muted mb-0">
                Associado ao serviço "{{ $servico->nome }}".
            </p>
        </div>
        <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.servicos.campos.index', $servico) }}">
            ← Voltar
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
            <form method="POST"
                  action="{{ $campo->exists ? route('admin.servicos.campos.update', [$servico, $campo]) : route('admin.servicos.campos.store', $servico) }}">
                @csrf
                @if($campo->exists)
                    @method('PUT')
                @endif

                <div class="row gy-3">
                    <div class="col-md-6">
                        <label class="form-label">Nome exibido</label>
                        <input type="text" name="nome_exibicao"
                               class="form-control @error('nome_exibicao') is-invalid @enderror"
                               value="{{ old('nome_exibicao', $campo->nome_exibicao) }}" required maxlength="120">
                        @error('nome_exibicao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Campo padrão</label>
                        <input type="text" name="campo_padrao"
                               class="form-control @error('campo_padrao') is-invalid @enderror"
                               value="{{ old('campo_padrao', $campo->campo_padrao) }}" required maxlength="120">
                        <small class="text-muted">Use nomes que batem com os dados do webhook (ex: email).</small>
                        @error('campo_padrao')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" class="form-select @error('tipo') is-invalid @enderror" required>
                            @foreach($tipos as $value => $label)
                                <option value="{{ $value }}"
                                    {{ old('tipo', $campo->tipo) === $value ? 'selected' : '' }}>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                        @error('tipo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-8">
                        <div class="form-check form-switch mt-3">
                            <input class="form-check-input" type="checkbox" role="switch" id="obrigatorio"
                                   name="obrigatorio" value="1"
                                   {{ old('obrigatorio', $campo->obrigatorio) ? 'checked' : '' }}>
                            <label class="form-check-label" for="obrigatorio">
                                Campo obrigatório
                            </label>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <button type="submit" class="btn btn-primary">
                        {{ $campo->exists ? 'Salvar alterações' : 'Adicionar campo' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
