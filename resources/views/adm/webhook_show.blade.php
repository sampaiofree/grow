@extends('adm.html_base')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col">
            <a href="{{ route('webhooks.index') }}" class="btn btn-link px-0">&larr; Voltar</a>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <form method="POST" action="{{ route('webhooks.update', $endpoint) }}">
                        @csrf
                        <h4 class="card-title mb-2">Editar endpoint</h4>
                        <p class="text-muted mb-2">UUID: {{ $endpoint->uuid }}</p>

                        <div class="mb-3">
                            <label class="form-label">Nome</label>
                            <input type="text" name="name" class="form-control @error('name') is-invalid @enderror"
                                   value="{{ old('name', $endpoint->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Serviço</label>
                            <select name="servico_id" class="form-select @error('servico_id') is-invalid @enderror" required>
                                <option value="">Selecione um serviço</option>
                                @foreach($servicos as $servico)
                                    <option value="{{ $servico->id }}"
                                        {{ (int) old('servico_id', $endpoint->servico_id) === $servico->id ? 'selected' : '' }}>
                                        {{ $servico->nome }} {{ $servico->slug ? "($servico->slug)" : '' }}
                                    </option>
                                @endforeach
                            </select>
                            @error('servico_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            @if($servicos->isEmpty())
                                <div class="alert alert-warning small mt-2 mb-0">
                                    Nenhum serviço disponível. @if(auth()->user()?->is_admin)
                                        <a class="alert-link" href="{{ route('admin.servicos.index') }}">Cadastre um serviço</a>.
                                    @else
                                        Peça para um administrador cadastrar um serviço ativo.
                                    @endif
                                </div>
                            @endif
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active"
                                   {{ old('is_active', $endpoint->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">Ativo</label>
                        </div>

                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <button type="submit" class="btn btn-primary btn-sm">Salvar alterações</button>
                            <span class="badge {{ $endpoint->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $endpoint->is_active ? 'Ativo' : 'Inativo' }}
                            </span>
                        </div>
                    </form>

                    @php
                        $publicUrl = rtrim(config('app.url'), '/').'/api/webhook/'.$endpoint->uuid;
                    @endphp
                    <label class="form-label">URL pública</label>
                    <div class="input-group mb-2">
                        <input type="text" class="form-control" value="{{ $publicUrl }}" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText('{{ $publicUrl }}')">Copiar</button>
                    </div>
                </div>
            </div>

            @if($requiredFields->isNotEmpty())
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Campos obrigatórios do serviço</h5>
                        <ul class="list-group list-group-flush">
                            @foreach($requiredFields as $field)
                                <li class="list-group-item px-0 d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>{{ $field->nome_exibicao }}</strong>
                                        <div class="small text-muted">{{ $field->campo_padrao }}</div>
                                    </div>
                                    <span class="badge bg-info text-dark">Fixado</span>
                                </li>
                            @endforeach
                        </ul>
                        <p class="text-muted small mt-3 mb-0">
                            Esses campos são criados automaticamente com base no serviço selecionado e não podem ser excluídos.
                        </p>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Payload de teste</h5>
                    <form method="POST" action="{{ route('webhooks.test', $endpoint) }}">
                        @csrf
                        <div class="mb-2">
                            <label class="form-label">JSON</label>
                            <textarea name="payload" class="form-control" rows="10" placeholder='{"campo":"valor"}'>{{ $endpoint->last_test_payload ? json_encode($endpoint->last_test_payload, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE) : '' }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-sm">Salvar teste</button>
                    </form>

                    @if(!empty($paths))
                        <hr>
                        <p class="text-muted mb-1">Campos detectados:</p>
                        <ul class="mb-0 small">
                            @foreach($paths as $p)
                                <li>{{ $p }}</li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-3">Mapeamento</h5>
                    <form method="POST" action="{{ route('webhooks.mappings.store', $endpoint) }}" id="mapping-form"
                          data-next-index="{{ $requiredFields->count() + $customMappings->count() }}">
                        @csrf

                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="mb-0">Campos obrigatórios</h6>
                                        <small class="text-muted">Gerados automaticamente pelo serviço</small>
                                    </div>
                                    <span class="badge bg-info text-dark">Fixos</span>
                                </div>

                                @php $mappingIndex = 0; @endphp
                                @if($requiredFields->isEmpty())
                                    <p class="text-muted">Nenhum campo obrigatório configurado para este serviço.</p>
                                @else
                                    <div class="row g-3 mb-0">
                                        @foreach($requiredFields as $field)
                                            @php
                                                $mapping = $mappings[$field->campo_padrao] ?? null;
                                                $selectedPaths = $mapping?->source_paths ?? [];
                                                $delimiter = $mapping?->delimiter ?? ' ';
                                            @endphp
                                            <div class="col-md-6">
                                                <div class="border rounded p-3 h-100" data-mapping-index="{{ $mappingIndex }}">
                                                    <input type="hidden" name="mappings[{{ $mappingIndex }}][target_key]" value="{{ $field->campo_padrao }}">
                                                    <input type="hidden" name="mappings[{{ $mappingIndex }}][mapping_id]" value="{{ $mapping?->id }}">
                                                    <input type="hidden" name="mappings[{{ $mappingIndex }}][is_locked]" value="1">
                                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                                        <div>
                                                            <strong>{{ $field->nome_exibicao }}</strong>
                                                            <div class="small text-muted">{{ $field->campo_padrao }}</div>
                                                        </div>
                                                        <span class="badge bg-secondary text-dark">Obrigatório</span>
                                                    </div>
                                                    <div class="mapping-badges mb-2" data-badges="{{ $mappingIndex }}">
                                                        @foreach($selectedPaths as $path)
                                                            <span class="badge bg-primary d-inline-flex align-items-center mb-1">
                                                                <span class="me-1">{{ $path }}</span>
                                                                <button type="button" class="btn-close btn-close-white btn-sm ms-1" aria-label="Remove" data-remove-badge></button>
                                                                <input type="hidden" name="mappings[{{ $mappingIndex }}][source_paths][]" value="{{ $path }}">
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                    @if(!empty($paths))
                                                        <div class="d-flex align-items-center gap-2">
                                                            <select class="form-select form-select-sm flex-grow-1" data-mapping-selector data-mapping-index="{{ $mappingIndex }}">
                                                                <option value="">Selecione um campo</option>
                                                                @foreach($paths as $path)
                                                                    <option value="{{ $path }}">{{ $path }}</option>
                                                                @endforeach
                                                            </select>
                                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-clear-mapping data-mapping-index="{{ $mappingIndex }}">Limpar</button>
                                                        </div>
                                                    @else
                                                        <p class="text-muted small mb-0">Salve um payload de teste para gerar campos.</p>
                                                    @endif
                                                    <div class="mt-2">
                                                        <label class="form-label small mb-1">Delimitador</label>
                                                        <select name="mappings[{{ $mappingIndex }}][delimiter]" class="form-select form-select-sm" style="width: 120px;">
                                                            @php $delimiters = [' ' => 'Espaço', ',' => 'Vírgula', '-' => 'Hífen']; @endphp
                                                        <option value="" {{ $delimiter === '' ? 'selected' : '' }}>Nenhum</option>
                                                        @foreach($delimiters as $char => $label)
                                                            <option value="{{ $char }}" {{ $delimiter === $char ? 'selected' : '' }}>{{ $label }}</option>
                                                        @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                            @php $mappingIndex++; @endphp
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <div>
                                        <h6 class="mb-0">Campos personalizados</h6>
                                        <small class="text-muted">Você pode adicionar ou remover campos extras</small>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-add-custom-field>Adicionar campo</button>
                                </div>

                                <div data-custom-mappings>
                                    @foreach($customMappings as $mapping)
                                        <div class="border rounded p-3 mb-3" data-mapping-index="{{ $mappingIndex }}">
                                            <input type="hidden" name="mappings[{{ $mappingIndex }}][mapping_id]" value="{{ $mapping->id }}">
                                            <input type="hidden" name="mappings[{{ $mappingIndex }}][is_locked]" value="0">
                                            <div class="row g-3 align-items-center mb-3">
                                                <div class="col-md-8">
                                                    <label class="form-label">Nome do campo</label>
                                                    <input type="text" name="mappings[{{ $mappingIndex }}][target_key]" class="form-control" value="{{ $mapping->target_key }}" required>
                                                </div>
                                                <div class="col-md-4 text-end">
                                                    <button type="button" class="btn btn-link text-danger" data-remove-custom data-mapping-id="{{ $mapping->id }}">
                                                        <i class="ri-delete-bin-line"></i> Excluir
                                                    </button>
                                                </div>
                                            </div>
                                            <div class="mapping-badges mb-2" data-badges="{{ $mappingIndex }}">
                                                @foreach($mapping->source_paths as $path)
                                                    <span class="badge bg-primary d-inline-flex align-items-center mb-1">
                                                        <span class="me-1">{{ $path }}</span>
                                                        <button type="button" class="btn-close btn-close-white btn-sm ms-1" aria-label="Remove" data-remove-badge></button>
                                                        <input type="hidden" name="mappings[{{ $mappingIndex }}][source_paths][]" value="{{ $path }}">
                                                    </span>
                                                @endforeach
                                            </div>
                                            @if(!empty($paths))
                                                <div class="d-flex align-items-center gap-2">
                                                    <select class="form-select form-select-sm flex-grow-1" data-mapping-selector data-mapping-index="{{ $mappingIndex }}">
                                                        <option value="">Selecione um campo</option>
                                                        @foreach($paths as $path)
                                                            <option value="{{ $path }}">{{ $path }}</option>
                                                        @endforeach
                                                    </select>
                                                    <button type="button" class="btn btn-sm btn-outline-secondary" data-clear-mapping data-mapping-index="{{ $mappingIndex }}">Limpar</button>
                                                </div>
                                            @else
                                                <p class="text-muted small mb-0">Salve um payload de teste para gerar campos.</p>
                                            @endif
                                            <div class="mt-2">
                                                <label class="form-label small mb-1">Delimitador</label>
                                                    <select name="mappings[{{ $mappingIndex }}][delimiter]" class="form-select form-select-sm" style="width: 120px;">
                                                        @php $delimiters = [' ' => 'Espaço', ',' => 'Vírgula', '-' => 'Hífen']; @endphp
                                                        <option value="" {{ $mapping->delimiter === '' ? 'selected' : '' }}>Nenhum</option>
                                                        @foreach($delimiters as $char => $label)
                                                            <option value="{{ $char }}" {{ $mapping->delimiter === $char ? 'selected' : '' }}>{{ $label }}</option>
                                                        @endforeach
                                                    </select>
                                            </div>
                                        </div>
                                        @php $mappingIndex++; @endphp
                                    @endforeach
                                </div>
                                <template id="custom-mapping-template">
                                    <div class="border rounded p-3 mb-3" data-mapping-index="__INDEX__">
                                        <input type="hidden" name="mappings[__INDEX__][is_locked]" value="0">
                                        <div class="row g-3 align-items-center mb-3">
                                            <div class="col-md-8">
                                                <label class="form-label">Nome do campo</label>
                                                <input type="text" name="mappings[__INDEX__][target_key]" class="form-control" value="" required>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <button type="button" class="btn btn-link text-danger" data-remove-custom>
                                                    <i class="ri-delete-bin-line"></i> Excluir
                                                </button>
                                            </div>
                                        </div>
                                        <div class="mapping-badges mb-2" data-badges="__INDEX__"></div>
                                        <div class="d-flex align-items-center gap-2">
                                            <select class="form-select form-select-sm flex-grow-1" data-mapping-selector data-mapping-index="__INDEX__">
                                                <option value="">Selecione um campo</option>
                                                @foreach($paths as $path)
                                                    <option value="{{ $path }}">{{ $path }}</option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-clear-mapping data-mapping-index="__INDEX__">Limpar</button>
                                        </div>
                                        <div class="mt-2">
                                            <label class="form-label small mb-1">Delimitador</label>
                                            <select name="mappings[__INDEX__][delimiter]" class="form-select form-select-sm" style="width: 120px;">
                                                @php $delimiters = [' ' => 'Espaço', ',' => 'Vírgula', '-' => 'Hífen']; @endphp
                                                <option value="">Nenhum</option>
                                                @foreach($delimiters as $char => $label)
                                                    <option value="{{ $char }}">{{ $label }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div id="deleted-mappings-container"></div>
                        <div class="mt-3 text-end">
                            <button type="submit" class="btn btn-primary">Salvar mapeamento</button>
                        </div>
                    </form>
                    <p class="text-muted small mt-2 mb-0">Regra: Arrays usam índice 0 (ex.: items.0.name). Se não mapear um campo, enviaremos vazio.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@section('body_end')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('mapping-form');
    if (!form) return;

    let nextIndex = Number(form.dataset.nextIndex) || 0;
    const deletedContainer = document.getElementById('deleted-mappings-container');
    const templateElement = document.getElementById('custom-mapping-template');

    const addBadge = (mappingIndex, value) => {
        if (!value) return;
        const badges = document.querySelector(`[data-badges="${mappingIndex}"]`);
        if (!badges) return;

        if (badges.querySelector(`input[value="${value}"]`)) {
            return;
        }

        const span = document.createElement('span');
        span.className = 'badge bg-primary d-inline-flex align-items-center mb-1';
        span.innerHTML = `<span class="me-1">${value}</span>
                          <button type="button" class="btn-close btn-close-white btn-sm ms-1" aria-label="Remove" data-remove-badge></button>`;

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = `mappings[${mappingIndex}][source_paths][]`;
        input.value = value;

        span.appendChild(input);
        badges.appendChild(span);
    };

    const clearMapping = (mappingIndex) => {
        const badges = document.querySelector(`[data-badges="${mappingIndex}"]`);
        if (badges) {
            badges.innerHTML = '';
        }
    };

    const removeCustomField = (button) => {
        const wrapper = button.closest('[data-mapping-index]');
        if (!wrapper) return;

        const mappingId = button.dataset.mappingId;
        if (mappingId) {
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'deleted_mappings[]';
            hidden.value = mappingId;
            deletedContainer.appendChild(hidden);
        }

        wrapper.remove();
    };

    const addCustomField = () => {
        if (!templateElement) return;

        const templateHtml = templateElement.innerHTML;
        const mappingHtml = templateHtml.replace(/__INDEX__/g, nextIndex);
        const wrapper = document.createElement('div');
        wrapper.innerHTML = mappingHtml;

        const customContainer = document.querySelector('[data-custom-mappings]');
        if (customContainer) {
            customContainer.appendChild(wrapper.firstElementChild);
        }

        nextIndex++;
        form.dataset.nextIndex = nextIndex;
    };

    document.addEventListener('change', (event) => {
        const select = event.target.closest('[data-mapping-selector]');
        if (!select) return;
        const index = select.dataset.mappingIndex;
        addBadge(index, select.value);
        select.value = '';
    });

    document.addEventListener('click', (event) => {
        const removeBadgeButton = event.target.closest('[data-remove-badge]');
        if (removeBadgeButton) {
            const badge = removeBadgeButton.closest('.badge');
            if (badge) badge.remove();
        }

        const clearButton = event.target.closest('[data-clear-mapping]');
        if (clearButton) {
            const index = clearButton.dataset.mappingIndex;
            clearMapping(index);
        }

        const customButton = event.target.closest('[data-add-custom-field]');
        if (customButton) {
            addCustomField();
        }

        const removeCustom = event.target.closest('[data-remove-custom]');
        if (removeCustom) {
            removeCustomField(removeCustom);
        }
    });
});
</script>
@endsection
@endsection
