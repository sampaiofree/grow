<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use FilesystemIterator;
use Illuminate\Http\Response;
use Illuminate\View\View;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class LogFileController extends Controller
{
    private const PREVIEW_BYTES = 500 * 1024;

    public function index(): View
    {
        $logs = [];
        $logsRoot = storage_path('logs');

        if (is_dir($logsRoot)) {
            $realRoot = realpath($logsRoot) ?: $logsRoot;

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($realRoot, FilesystemIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if (! $file->isFile()) {
                    continue;
                }

                $absolutePath = $file->getPathname();
                $relativePath = ltrim(str_replace('\\', '/', substr($absolutePath, strlen($realRoot))), '/');
                $size = (int) $file->getSize();
                $modifiedAtTs = (int) $file->getMTime();

                $logs[] = [
                    'basename' => $file->getFilename(),
                    'relative_path' => $relativePath,
                    'size' => $size,
                    'size_human' => $this->formatBytes($size),
                    'modified_at' => date('d/m/Y H:i:s', $modifiedAtTs),
                    'modified_at_ts' => $modifiedAtTs,
                ];
            }

            usort($logs, fn (array $a, array $b) => $b['modified_at_ts'] <=> $a['modified_at_ts']);
        }

        return view('adm.logs.index', [
            'logs' => $logs,
            'previewLimitBytes' => self::PREVIEW_BYTES,
        ]);
    }

    public function download(string $path): BinaryFileResponse
    {
        $filePath = $this->resolveLogFilePath($path);

        return response()->download($filePath, basename($filePath));
    }

    public function preview(string $path): Response
    {
        $filePath = $this->resolveLogFilePath($path);
        [$content, $truncated, $fileSize] = $this->readLastBytes($filePath, self::PREVIEW_BYTES);

        return response($content, 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
            'Pragma' => 'no-cache',
            'X-Log-File-Size' => (string) $fileSize,
            'X-Log-Preview-Limit' => (string) self::PREVIEW_BYTES,
            'X-Log-Preview-Truncated' => $truncated ? '1' : '0',
        ]);
    }

    private function resolveLogFilePath(string $relativePath): string
    {
        abort_if($relativePath === '' || str_contains($relativePath, "\0"), 404);

        $logsRoot = realpath(storage_path('logs'));
        abort_if($logsRoot === false, 404);

        $resolvedPath = realpath($logsRoot.DIRECTORY_SEPARATOR.$relativePath);
        abort_if($resolvedPath === false || ! is_file($resolvedPath), 404);

        $normalizedRoot = rtrim(str_replace('\\', '/', $logsRoot), '/');
        $normalizedResolved = str_replace('\\', '/', $resolvedPath);

        abort_if(! str_starts_with($normalizedResolved, $normalizedRoot.'/'), 404);

        return $resolvedPath;
    }

    private function readLastBytes(string $filePath, int $maxBytes): array
    {
        $fileSize = filesize($filePath);
        abort_if($fileSize === false, 500, 'Nao foi possivel ler o arquivo de log.');

        $bytesToRead = min((int) $fileSize, $maxBytes);

        if ($bytesToRead === 0) {
            return ['', false, (int) $fileSize];
        }

        $handle = fopen($filePath, 'rb');
        abort_if($handle === false, 500, 'Nao foi possivel abrir o arquivo de log.');

        try {
            fseek($handle, -$bytesToRead, SEEK_END);
            $content = fread($handle, $bytesToRead);
        } finally {
            fclose($handle);
        }

        abort_if($content === false, 500, 'Nao foi possivel ler o conteudo do arquivo de log.');

        return [$content, (int) $fileSize > $maxBytes, (int) $fileSize];
    }

    private function formatBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        $units = ['KB', 'MB', 'GB', 'TB'];
        $size = $bytes / 1024;
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return number_format($size, $size >= 10 ? 0 : 1, ',', '.').' '.$units[$unitIndex];
    }
}
