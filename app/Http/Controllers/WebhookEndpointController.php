<?php

namespace App\Http\Controllers;

use App\Models\WebhookEndpoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WebhookEndpointController extends Controller
{
    public function index()
    {
        $endpoints = WebhookEndpoint::where('user_id', Auth::id())
            ->latest()
            ->get();

        return view('adm.webhooks', compact('endpoints'));
    }

    public function show(WebhookEndpoint $endpoint)
    {
        abort_if($endpoint->user_id !== Auth::id(), 403);

        $paths = $this->flattenPayload($endpoint->last_test_payload ?? []);

        $mappings = $endpoint->mappings()
            ->get()
            ->keyBy('target_key');

        $targets = [
            'first_name', 'last_name', 'phone', 'whatsapp_phone', 'email',
            'boleto_link', 'pix_codigo', 'produto_nome', 'produto_valor',
            'recuperacao_url', 'transacao_codigo', 'status',
            'cliente_email', 'cliente_senha', 'links_member',
        ];

        return view('adm.webhook_show', compact('endpoint', 'paths', 'mappings', 'targets'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|in:on,1,0,true,false',
        ]);

        WebhookEndpoint::create([
            'user_id' => Auth::id(),
            'uuid' => (string) Str::uuid(),
            'name' => $data['name'],
            'is_active' => $request->boolean('is_active', true),
        ]);

        return back()->with('success', 'Endpoint criado com sucesso.');
    }

    public function update(Request $request, WebhookEndpoint $endpoint)
    {
        abort_if($endpoint->user_id !== Auth::id(), 403);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'is_active' => 'nullable|in:on,1,0,true,false',
        ]);

        $endpoint->name = $data['name'];
        $endpoint->is_active = $request->boolean('is_active', true);
        $endpoint->disabled_at = $endpoint->is_active ? null : now();
        $endpoint->save();

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
