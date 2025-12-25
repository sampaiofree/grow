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
                    <h4 class="card-title mb-2">{{ $endpoint->name }}</h4>
                    <p class="text-muted mb-2">UUID: {{ $endpoint->uuid }}</p>
                    @php
                        $publicUrl = rtrim(config('app.url'), '/').'/api/webhook/'.$endpoint->uuid;
                    @endphp
                    <label class="form-label">URL pública</label>
                    <div class="input-group mb-2">
                        <input type="text" class="form-control" value="{{ $publicUrl }}" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="navigator.clipboard.writeText('{{ $publicUrl }}')">Copiar</button>
                    </div>
                    <span class="badge {{ $endpoint->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $endpoint->is_active ? 'Ativo' : 'Inativo' }}</span>
                </div>
            </div>

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
                    <form method="POST" action="{{ route('webhooks.mappings.store', $endpoint) }}" id="mapping-form">
                        @csrf
                        <div class="row g-3">
                            @foreach($targets as $target)
                                @php
                                    $existing = $mappings[$target] ?? null;
                                    $selected = $existing?->source_paths ?? [];
                                    $delimiter = $existing?->delimiter ?? ' ';
                                @endphp
                                <div class="col-md-6">
                                    <label class="form-label">{{ $target }}</label>
                                    <div class="border rounded p-2" data-target="{{ $target }}">
                                        <div class="d-flex flex-wrap gap-1 mb-2 mapping-badges" data-badges="{{ $target }}">
                                            @foreach($selected as $sel)
                                                <span class="badge bg-primary d-inline-flex align-items-center">
                                                    <span class="me-1">{{ $sel }}</span>
                                                    <button type="button" class="btn-close btn-close-white btn-sm ms-1" aria-label="Remove" data-remove-badge></button>
                                                    <input type="hidden" name="mappings[{{ $loop->index }}][source_paths][]" value="{{ $sel }}">
                                                </span>
                                            @endforeach
                                        </div>
                                        @if(!empty($paths))
                                            <div class="d-flex align-items-center gap-2">
                                                <select class="form-select form-select-sm flex-grow-1" data-selector="{{ $target }}">
                                                    <option value="">Selecione um campo</option>
                                                    @foreach($paths as $path)
                                                        <option value="{{ $path }}">{{ $path }}</option>
                                                    @endforeach
                                                </select>
                                                <button type="button" class="btn btn-sm btn-outline-secondary" data-clear="{{ $target }}">Limpar</button>
                                            </div>
                                        @else
                                            <p class="text-muted small mb-0">Salve um payload de teste para gerar campos.</p>
                                        @endif
                                    </div>
                                    <input type="hidden" name="mappings[{{ $loop->index }}][target_key]" value="{{ $target }}">
                                    <div class="mt-1 d-flex align-items-center gap-2">
                                        <span class="text-muted small">Separador</span>
                                        <select class="form-select form-select-sm" style="width: 80px;" name="mappings[{{ $loop->index }}][delimiter]">
                                            @php $delimiters = [' ' => 'Espaço', ',' => 'Vírgula', '-' => 'Hífen']; @endphp
                                            <option value="" {{ $delimiter === '' ? 'selected' : '' }}>Nenhum</option>
                                            @foreach($delimiters as $char => $label)
                                                <option value="{{ $char }}" {{ $delimiter === $char ? 'selected' : '' }}>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endforeach
                        </div>
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
    // indexa os containers para manter o name correto dos inputs
    document.querySelectorAll('[data-badges]').forEach((container, idx) => {
        container.dataset.index = idx;
    });

    const addBadge = (target, value) => {
        if (!value) return;
        const badges = document.querySelector(`[data-badges="${target}"]`);
        if (!badges) return;

        // evita duplicados
        const exists = badges.querySelector(`input[value="${value}"]`);
        if (exists) return;

        const span = document.createElement('span');
        span.className = 'badge bg-primary d-inline-flex align-items-center';
        span.innerHTML = `<span class="me-1">${value}</span>
                          <button type="button" class="btn-close btn-close-white btn-sm ms-1" aria-label="Remove" data-remove-badge></button>
                          <input type="hidden" name="mappings[${badges.dataset.index || 0}][source_paths][]" value="${value}">`;
        badges.appendChild(span);
    };

    document.querySelectorAll('[data-selector]').forEach(select => {
        const target = select.dataset.selector;
        select.addEventListener('change', (e) => {
            addBadge(target, e.target.value);
            e.target.value = '';
        });
    });

    document.addEventListener('click', (e) => {
        const removeBtn = e.target.closest('[data-remove-badge]');
        if (removeBtn) {
            const badge = removeBtn.closest('.badge');
            if (badge) badge.remove();
        }
        if (e.target.matches('[data-clear]')) {
            const target = e.target.getAttribute('data-clear');
            const badges = document.querySelector(`[data-badges="${target}"]`);
            if (badges) badges.innerHTML = '';
        }
    });
});
</script>
@endsection
@endsection
