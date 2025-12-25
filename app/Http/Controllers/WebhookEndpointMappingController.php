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

        $targets = [
            'first_name', 'last_name', 'phone', 'whatsapp_phone', 'email',
            'boleto_link', 'pix_codigo', 'produto_nome', 'produto_valor',
            'recuperacao_url', 'transacao_codigo', 'status',
            'cliente_email', 'cliente_senha', 'links_member',
        ];

        $data = $request->validate([
            'mappings' => 'required|array',
            'mappings.*.target_key' => 'required|string|in:'.implode(',', $targets),
            'mappings.*.source_paths' => 'nullable|array',
            'mappings.*.source_paths.*' => 'string',
            'mappings.*.delimiter' => 'nullable|string|max:5',
        ]);

        // Apaga mappings antigos e recria
        $endpoint->mappings()->delete();

        foreach ($data['mappings'] as $map) {
            $endpoint->mappings()->create([
                'target_key' => $map['target_key'],
                'source_paths' => $map['source_paths'] ?? [],
                'delimiter' => $map['delimiter'] ?? ' ',
            ]);
        }

        return back()->with('success', 'Mapeamento salvo com sucesso.');
    }
}
