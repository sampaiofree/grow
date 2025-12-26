<?php

namespace App\Http\Controllers;

use App\Models\Servico;
use App\Models\ServicoCampoObrigatorio;
use App\Models\WebhookEndpoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WebhookEndpointController extends Controller
{
    public function index()
    {
        $endpoints = WebhookEndpoint::with('servico')
            ->where('user_id', Auth::id())
            ->latest()
            ->get();

        $servicos = Servico::where('ativo', true)
            ->orderBy('nome')
            ->get();

        return view('adm.webhooks', compact('endpoints', 'servicos'));
    }

    public function show(WebhookEndpoint $endpoint)
    {
        abort_if($endpoint->user_id !== Auth::id(), 403);

        $endpoint->loadMissing('servico');

        $paths = $this->flattenPayload($endpoint->last_test_payload ?? []);

        $mappings = $endpoint->mappings()
            ->get()
            ->keyBy('target_key');

        $servicos = Servico::where('ativo', true)
            ->orderBy('nome')
            ->get();

        $requiredFields = $endpoint->servico
            ? ServicoCampoObrigatorio::where('servico_id', $endpoint->servico->id)
                ->where('obrigatorio', true)
                ->get()
            : collect();

        $customMappings = $endpoint->mappings()
            ->where('is_locked', false)
            ->get();

        return view('adm.webhook_show', compact('endpoint', 'paths', 'mappings', 'servicos', 'requiredFields', 'customMappings'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|in:on,1,0,true,false',
            'servico_id' => 'required|exists:servicos,id',
        ]);

        $endpoint = WebhookEndpoint::create([
            'user_id' => Auth::id(),
            'uuid' => (string) Str::uuid(),
            'name' => $data['name'],
            'is_active' => $request->boolean('is_active', true),
            'servico_id' => $data['servico_id'],
        ]);

        $this->syncRequiredMappings($endpoint);

        return back()->with('success', 'Endpoint criado com sucesso.');
    }

    public function update(Request $request, WebhookEndpoint $endpoint)
    {
        abort_if($endpoint->user_id !== Auth::id(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|in:on,1,0,true,false',
            'servico_id' => 'required|exists:servicos,id',
        ]);

        $endpoint->name = $data['name'];
        $endpoint->is_active = $request->boolean('is_active', true);
        $endpoint->servico_id = $data['servico_id'];
        $endpoint->disabled_at = $endpoint->is_active ? null : now();
        $endpoint->save();

        $this->syncRequiredMappings($endpoint);

        return back()->with('success', 'Endpoint atualizado com sucesso.');
    }

    public function saveTestPayload(Request $request, WebhookEndpoint $endpoint)
    {
        abort_if($endpoint->user_id !== Auth::id(), 403);

        $data = $request->validate([
            'payload' => 'required|json',
        ]);

        $endpoint->last_test_payload = json_decode($data['payload'], true);
        $endpoint->save();

        return back()->with('success', 'Payload de teste salvo com sucesso.');
    }

    public function destroy(WebhookEndpoint $endpoint)
    {
        abort_if($endpoint->user_id !== Auth::id(), 403);

        $endpoint->delete();

        return redirect()->route('webhooks.index')->with('success', 'Endpoint removido com sucesso.');
    }

    private function syncRequiredMappings(WebhookEndpoint $endpoint): void
    {
        if (! $endpoint->servico_id) {
            return;
        }

        $requiredFields = ServicoCampoObrigatorio::where('servico_id', $endpoint->servico_id)
            ->where('obrigatorio', true)
            ->pluck('campo_padrao')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $existingLocked = $endpoint->mappings()
            ->where('is_locked', true)
            ->pluck('target_key')
            ->toArray();

        $toRemove = array_diff($existingLocked, $requiredFields);
        $toAdd = array_diff($requiredFields, $existingLocked);

        if (! empty($toRemove)) {
            $endpoint->mappings()
                ->where('is_locked', true)
                ->whereIn('target_key', $toRemove)
                ->delete();
        }

        foreach ($toAdd as $target) {
            $endpoint->mappings()->create([
                'target_key' => $target,
                'source_paths' => [],
                'delimiter' => ' ',
                'is_locked' => true,
            ]);
        }
    }

    private function flattenPayload(array $payload, string $prefix = ''): array
    {
        $paths = [];
        foreach ($payload as $key => $value) {
            $path = $prefix === '' ? $key : $prefix.'.'.$key;
            if (is_array($value)) {
                // Se for lista numérica, só index 0 para simplificar
                if ($this->isList($value) && isset($value[0])) {
                    foreach ($this->flattenPayload($value[0], $path.'.0') as $p) {
                        $paths[] = $p;
                    }
                } else {
                    $paths = array_merge($paths, $this->flattenPayload($value, $path));
                }
            } else {
                $paths[] = $path;
            }
        }
        return $paths;
    }

    private function isList(array $array): bool
    {
        return array_keys($array) === range(0, count($array) - 1);
    }
}
