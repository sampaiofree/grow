<?php

namespace App\Http\Controllers;

use App\Models\WebhookEndpoint;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class WebhookEndpointMappingController extends Controller
{
    public function store(Request $request, WebhookEndpoint $endpoint)
    {
        abort_if($endpoint->user_id !== Auth::id(), 403);

        $request->validate([
            'mappings' => 'required|array',
            'mappings.*.target_key' => 'required|string',
            'mappings.*.source_paths' => 'nullable|array',
            'mappings.*.source_paths.*' => 'string',
            'mappings.*.delimiter' => 'nullable|string|max:5',
            'mappings.*.value_template' => 'nullable|string',
            'mappings.*.mapping_id' => 'nullable|integer',
            'mappings.*.is_locked' => 'nullable|boolean',
        ]);

        $deletedIds = array_filter((array) $request->input('deleted_mappings', []), 'is_numeric');
        if (!empty($deletedIds)) {
            $endpoint->mappings()
                ->whereIn('id', $deletedIds)
                ->where('is_locked', false)
                ->delete();
        }

        foreach ($request->input('mappings', []) as $mappingData) {
            $target = trim($mappingData['target_key'] ?? '');
            if ($target === '') {
                continue;
            }

            $paths = array_values(array_filter((array) ($mappingData['source_paths'] ?? []), fn($value) => $value !== ''));
            $delimiter = $mappingData['delimiter'] ?? ' ';
            $isLocked = filter_var($mappingData['is_locked'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $mappingId = $mappingData['mapping_id'] ?? null;
            $template = (string) ($mappingData['value_template'] ?? '');
            if (trim($template) === '' && ! empty($paths)) {
                $template = $this->buildTemplateFromPaths($paths, (string) $delimiter);
            }

            $payload = [
                'target_key' => $target,
                'source_paths' => $paths,
                'delimiter' => $delimiter,
                'value_template' => $template,
                'is_locked' => $isLocked,
            ];

            if ($mappingId && $mapping = $endpoint->mappings()->find($mappingId)) {
                $mapping->update($payload);
                continue;
            }

            $endpoint->mappings()->create($payload);
        }

        return back()->with('success', 'Mapeamento salvo com sucesso.');
    }

    private function buildTemplateFromPaths(array $paths, string $delimiter): string
    {
        $tokens = array_map(fn($path) => '{{'.$path.'}}', $paths);

        return implode($delimiter, $tokens);
    }
}
