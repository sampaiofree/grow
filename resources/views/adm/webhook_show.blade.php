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
                        @php
                            $tokenPaths = array_values(array_unique(array_merge(['payload'], $paths ?? [])));
                        @endphp

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
                                                $template = $mapping?->value_template ?? '';
                                                if ($template === '' && ! empty($selectedPaths)) {
                                                    $template = implode($delimiter, array_map(fn($path) => '{{'.$path.'}}', $selectedPaths));
                                                }
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
                                                    <div class="mb-2" data-template-wrapper data-mapping-index="{{ $mappingIndex }}">
                                                        <label class="form-label small mb-1">Template</label>
                                                        <div class="form-control template-editor" contenteditable="true" data-template-editor
                                                             data-mapping-index="{{ $mappingIndex }}" data-placeholder="Digite texto e insira campos"></div>
                                                        <input type="hidden" name="mappings[{{ $mappingIndex }}][value_template]" data-template-input
                                                               value="{{ $template }}">
                                                        <input type="hidden" name="mappings[{{ $mappingIndex }}][delimiter]" value="{{ $delimiter }}">
                                                    </div>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <select class="form-select form-select-sm flex-grow-1" data-token-select data-mapping-index="{{ $mappingIndex }}">
                                                            <option value="">Inserir campo do payload</option>
                                                            @foreach($tokenPaths as $path)
                                                                <option value="{{ $path }}">{{ $path }}</option>
                                                            @endforeach
                                                        </select>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary" data-clear-template data-mapping-index="{{ $mappingIndex }}">Limpar</button>
                                                    </div>
                                                    @if(empty($paths))
                                                        <p class="text-muted small mb-0">Salve um payload de teste para gerar mais campos.</p>
                                                    @endif
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
                                        @php
                                            $selectedPaths = $mapping->source_paths ?? [];
                                            $delimiter = $mapping->delimiter ?? ' ';
                                            $template = $mapping->value_template ?? '';
                                            if ($template === '' && ! empty($selectedPaths)) {
                                                $template = implode($delimiter, array_map(fn($path) => '{{'.$path.'}}', $selectedPaths));
                                            }
                                        @endphp
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
                                            <div class="mb-2" data-template-wrapper data-mapping-index="{{ $mappingIndex }}">
                                                <label class="form-label small mb-1">Template</label>
                                                <div class="form-control template-editor" contenteditable="true" data-template-editor
                                                     data-mapping-index="{{ $mappingIndex }}" data-placeholder="Digite texto e insira campos"></div>
                                                <input type="hidden" name="mappings[{{ $mappingIndex }}][value_template]" data-template-input
                                                       value="{{ $template }}">
                                                <input type="hidden" name="mappings[{{ $mappingIndex }}][delimiter]" value="{{ $delimiter }}">
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <select class="form-select form-select-sm flex-grow-1" data-token-select data-mapping-index="{{ $mappingIndex }}">
                                                    <option value="">Inserir campo do payload</option>
                                                    @foreach($tokenPaths as $path)
                                                        <option value="{{ $path }}">{{ $path }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" data-clear-template data-mapping-index="{{ $mappingIndex }}">Limpar</button>
                                            </div>
                                            @if(empty($paths))
                                                <p class="text-muted small mb-0">Salve um payload de teste para gerar mais campos.</p>
                                            @endif
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
                                        <div class="mb-2" data-template-wrapper data-mapping-index="__INDEX__">
                                            <label class="form-label small mb-1">Template</label>
                                            <div class="form-control template-editor" contenteditable="true" data-template-editor
                                                 data-mapping-index="__INDEX__" data-placeholder="Digite texto e insira campos"></div>
                                            <input type="hidden" name="mappings[__INDEX__][value_template]" data-template-input value="">
                                            <input type="hidden" name="mappings[__INDEX__][delimiter]" value=" ">
                                        </div>
                                        <div class="d-flex align-items-center gap-2">
                                            <select class="form-select form-select-sm flex-grow-1" data-token-select data-mapping-index="__INDEX__">
                                                <option value="">Inserir campo do payload</option>
                                                @foreach($tokenPaths as $path)
                                                    <option value="{{ $path }}">{{ $path }}</option>
                                                @endforeach
                                            </select>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" data-clear-template data-mapping-index="__INDEX__">Limpar</button>
                                        </div>
                                        @if(empty($paths))
                                            <p class="text-muted small mb-0">Salve um payload de teste para gerar mais campos.</p>
                                        @endif
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div id="deleted-mappings-container"></div>
                        <div class="mt-3 text-end">
                            <button type="submit" class="btn btn-primary">Salvar mapeamento</button>
                        </div>
                    </form>
                    <p class="text-muted small mt-2 mb-0">Regra: listas usam indice (ex.: items.0.name). Use @{{path}} para inserir dados e @{{payload}} para o JSON completo.</p>
                </div>
            </div>
        </div>
    </div>
</div>

@section('body_end')
<style>
.template-editor {
    min-height: 38px;
    white-space: pre-wrap;
}

.template-editor:empty:before {
    content: attr(data-placeholder);
    color: #6c757d;
}

.token-badge {
    cursor: default;
}

.token-remove {
    line-height: 1;
}
</style>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('mapping-form');
    if (!form) return;

    let nextIndex = Number(form.dataset.nextIndex) || 0;
    const deletedContainer = document.getElementById('deleted-mappings-container');
    const templateElement = document.getElementById('custom-mapping-template');

    const createToken = (path) => {
        const token = document.createElement('span');
        token.className = 'badge bg-primary token-badge d-inline-flex align-items-center mb-1';
        token.setAttribute('contenteditable', 'false');
        token.dataset.tokenPath = path;

        const text = document.createElement('span');
        text.className = 'me-1';
        text.textContent = path;

        const remove = document.createElement('button');
        remove.type = 'button';
        remove.className = 'btn btn-sm btn-link p-0 text-white token-remove';
        remove.setAttribute('aria-label', 'Remove');
        remove.textContent = 'x';

        token.appendChild(text);
        token.appendChild(remove);

        return token;
    };

    const renderTemplate = (editor, template) => {
        editor.innerHTML = '';
        if (!template) {
            return;
        }

        const splitPattern = new RegExp('(\\{\\{[^}]+\\}\\})', 'g');
        const tokenPattern = new RegExp('^\\{\\{([^}]+)\\}\\}$');
        const parts = template.split(splitPattern);

        parts.forEach((part) => {
            if (!part) return;
            const match = part.match(tokenPattern);
            if (match) {
                const path = match[1].trim();
                if (path) {
                    editor.appendChild(createToken(path));
                } else {
                    editor.appendChild(document.createTextNode(part));
                }
                return;
            }

            editor.appendChild(document.createTextNode(part));
        });
    };

    const serializeTemplate = (editor) => {
        const clone = editor.cloneNode(true);
        clone.querySelectorAll('[data-token-path]').forEach((el) => {
            const path = el.dataset.tokenPath || '';
            const text = document.createTextNode('{' + '{' + path + '}' + '}');
            el.replaceWith(text);
        });

        return clone.textContent || '';
    };

    const syncTemplate = (editor) => {
        const wrapper = editor.closest('[data-template-wrapper]');
        if (!wrapper) return;
        const input = wrapper.querySelector('[data-template-input]');
        if (!input) return;
        input.value = serializeTemplate(editor).trim();
    };

    const insertToken = (editor, path) => {
        if (!path) return;
        const token = createToken(path);
        const space = document.createTextNode(' ');
        const selection = window.getSelection();

        if (selection && selection.rangeCount > 0) {
            const range = selection.getRangeAt(0);
            if (!editor.contains(range.commonAncestorContainer)) {
                editor.append(token, space);
            } else {
                range.deleteContents();
                const fragment = document.createDocumentFragment();
                fragment.appendChild(token);
                fragment.appendChild(space);
                range.insertNode(fragment);
                range.setStartAfter(space);
                range.collapse(true);
                selection.removeAllRanges();
                selection.addRange(range);
            }
        } else {
            editor.append(token, space);
        }

        syncTemplate(editor);
    };

    const clearTemplate = (editor) => {
        editor.innerHTML = '';
        syncTemplate(editor);
    };

    const setupEditor = (editor) => {
        const wrapper = editor.closest('[data-template-wrapper]');
        if (!wrapper) return;
        const input = wrapper.querySelector('[data-template-input]');
        renderTemplate(editor, input ? input.value : '');
        syncTemplate(editor);
        editor.addEventListener('input', () => syncTemplate(editor));
        editor.addEventListener('blur', () => syncTemplate(editor));
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
        if (customContainer && wrapper.firstElementChild) {
            customContainer.appendChild(wrapper.firstElementChild);
            const editor = customContainer.querySelector(`[data-template-editor][data-mapping-index="${nextIndex}"]`);
            if (editor) {
                setupEditor(editor);
            }
        }

        nextIndex++;
        form.dataset.nextIndex = nextIndex;
    };

    form.querySelectorAll('[data-template-editor]').forEach(setupEditor);

    document.addEventListener('change', (event) => {
        const select = event.target.closest('[data-token-select]');
        if (!select) return;
        const index = select.dataset.mappingIndex;
        const editor = form.querySelector(`[data-template-editor][data-mapping-index="${index}"]`);
        if (editor) {
            insertToken(editor, select.value);
        }
        select.value = '';
    });

    document.addEventListener('click', (event) => {
        const removeToken = event.target.closest('.token-remove');
        if (removeToken) {
            const token = removeToken.closest('[data-token-path]');
            if (token) {
                const editor = token.closest('[data-template-editor]');
                token.remove();
                if (editor) syncTemplate(editor);
            }
        }

        const clearButton = event.target.closest('[data-clear-template]');
        if (clearButton) {
            const index = clearButton.dataset.mappingIndex;
            const editor = form.querySelector(`[data-template-editor][data-mapping-index="${index}"]`);
            if (editor) {
                clearTemplate(editor);
            }
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
