@extends('adm.html_base')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <div>
                            <h4 class="card-title mb-1">Logs do sistema</h4>
                            <p class="text-muted mb-0">
                                Arquivos em <code>storage/logs</code>. O botão "Ver" mostra os últimos
                                {{ number_format($previewLimitBytes / 1024, 0, ',', '.') }} KB.
                            </p>
                        </div>
                    </div>

                    @if(empty($logs))
                        <div class="alert alert-warning mb-0">
                            Nenhum arquivo encontrado em <code>storage/logs</code>.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-striped table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Arquivo</th>
                                        <th>Tamanho</th>
                                        <th>Última modificação</th>
                                        <th class="text-end" style="width: 220px;">Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($logs as $log)
                                        <tr>
                                            <td>
                                                <div class="fw-semibold">{{ $log['basename'] }}</div>
                                                @if($log['relative_path'] !== $log['basename'])
                                                    <small class="text-muted d-block">{{ $log['relative_path'] }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $log['size_human'] }}</td>
                                            <td>{{ $log['modified_at'] }}</td>
                                            <td class="text-end">
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-secondary btn-log-preview me-1"
                                                    data-preview-url="{{ route('adm.logs.preview', ['path' => $log['relative_path']]) }}"
                                                    data-file-name="{{ $log['relative_path'] }}"
                                                >
                                                    Ver
                                                </button>
                                                <a
                                                    href="{{ route('adm.logs.download', ['path' => $log['relative_path']]) }}"
                                                    class="btn btn-sm btn-outline-primary"
                                                >
                                                    Baixar
                                                </a>
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

<div class="modal fade" id="logPreviewModal" tabindex="-1" aria-labelledby="logPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="logPreviewModalLabel">Preview de log</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="logPreviewInfo" class="small text-muted mb-2">Selecione um arquivo para visualizar.</div>
                <pre
                    id="logPreviewContent"
                    class="border rounded bg-light p-3 mb-0"
                    style="max-height: 70vh; overflow: auto; white-space: pre-wrap; word-break: break-word;"
                >Selecione um arquivo para visualizar.</pre>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('body_end')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('logPreviewModal');
    const modalTitle = document.getElementById('logPreviewModalLabel');
    const previewInfo = document.getElementById('logPreviewInfo');
    const previewContent = document.getElementById('logPreviewContent');
    const previewLimitBytes = {{ (int) $previewLimitBytes }};
    let activeRequestId = 0;

    if (!modalElement || !window.bootstrap || !window.bootstrap.Modal) {
        return;
    }

    const modalInstance = new window.bootstrap.Modal(modalElement);

    function formatBytes(bytes) {
        if (!Number.isFinite(bytes) || bytes < 0) {
            return '-';
        }

        if (bytes < 1024) {
            return bytes + ' B';
        }

        const units = ['KB', 'MB', 'GB', 'TB'];
        let size = bytes / 1024;
        let unitIndex = 0;

        while (size >= 1024 && unitIndex < units.length - 1) {
            size = size / 1024;
            unitIndex++;
        }

        const decimals = size >= 10 ? 0 : 1;
        return size.toLocaleString('pt-BR', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals
        }) + ' ' + units[unitIndex];
    }

    async function loadPreview(button) {
        const fileName = button.dataset.fileName || 'Arquivo';
        const previewUrl = button.dataset.previewUrl;
        const requestId = ++activeRequestId;

        modalTitle.textContent = 'Preview: ' + fileName;
        previewInfo.textContent = 'Carregando preview...';
        previewContent.textContent = 'Carregando...';
        modalInstance.show();

        try {
            const response = await fetch(previewUrl, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                cache: 'no-store'
            });

            if (!response.ok) {
                throw new Error('Falha ao carregar o preview (' + response.status + ').');
            }

            const text = await response.text();

            if (requestId !== activeRequestId) {
                return;
            }

            const truncated = response.headers.get('X-Log-Preview-Truncated') === '1';
            const fileSize = Number(response.headers.get('X-Log-File-Size') || 0);
            const limit = Number(response.headers.get('X-Log-Preview-Limit') || previewLimitBytes);
            const limitLabel = formatBytes(limit);
            const fileSizeLabel = formatBytes(fileSize);

            previewInfo.textContent = truncated
                ? 'Exibindo os últimos ' + limitLabel + ' de um arquivo com ' + fileSizeLabel + '.'
                : 'Exibindo o arquivo completo (' + fileSizeLabel + ').';

            previewContent.textContent = text || '[Arquivo vazio]';
            previewContent.scrollTop = previewContent.scrollHeight;
        } catch (error) {
            if (requestId !== activeRequestId) {
                return;
            }

            previewInfo.textContent = 'Erro ao carregar preview.';
            previewContent.textContent = error instanceof Error
                ? error.message
                : 'Erro inesperado ao carregar o preview.';
        }
    }

    document.addEventListener('click', function (event) {
        const button = event.target.closest('.btn-log-preview');

        if (!button) {
            return;
        }

        event.preventDefault();
        loadPreview(button);
    });
});
</script>
@endsection
