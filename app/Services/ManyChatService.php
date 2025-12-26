<?php

namespace App\Services;

use App\Models\WebhookEndpoint;
use App\Services\Contracts\WebhookHandlerInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class ManyChatService implements WebhookHandlerInterface
{
    protected string $accessToken = '';

    /**
     * Campos de sistema aceitos pelo endpoint de criação do ManyChat.
     */
    protected array $systemFields = [
        'first_name',
        'last_name',
        'phone',
        'whatsapp_phone',
        'email',
    ];

    public function handle(WebhookEndpoint $endpoint, array $payload): JsonResponse
    {
        $user = $endpoint->user;
        if (! $user || empty($user->many_access_token)) {
            return response()->json([
                'message' => 'Token do ManyChat não configurado',
            ], 422);
        }

        $this->accessToken = $user->many_access_token;

        $data = $payload;
        $this->normalizeSystemFields($data);

        return $this->many($data);
    }

    protected function many(array $dados): JsonResponse
    {
        if (empty($dados['email'])) {
            return response()->json([
                'message' => 'Email obrigatório para identificar o contato no ManyChat.',
            ], 422);
        }

        $result = $this->findSubscriberId($dados['email']);
        if (isset($result['error'])) {
            return $result['error'];
        }

        if (! empty($result['id'])) {
            return $this->manyFiel($result['id'], $dados);
        }

        $subscriberData = array_intersect_key($dados, array_flip($this->systemFields));

        $response = Http::withToken($this->accessToken)
            ->post('https://api.manychat.com/fb/subscriber/createSubscriber', $subscriberData);

        if (! $response->successful()) {
            return $this->respondFromManyChat($response);
        }

        $subscriberId = data_get($response->json(), 'data.id');
        if (! $subscriberId) {
            return response()->json([
                'message' => 'Resposta inesperada ao criar contato no ManyChat.',
                'data' => $response->json(),
            ], 500);
        }

        return $this->manyFiel($subscriberId, $dados);
    }

    protected function manyFiel(string $id, array $dados): JsonResponse
    {
        $fields = [];
        foreach ($dados as $campo => $valor) {
            if (in_array($campo, $this->systemFields, true)) {
                continue;
            }

            if ($valor === null || $valor === '') {
                continue;
            }

            $fields[] = [
                'field_name' => $campo,
                'field_value' => is_scalar($valor) ? (string) $valor : json_encode($valor),
            ];
        }

        if (empty($fields)) {
            return response()->json([
                'message' => 'Nenhum campo personalizado para atualizar.',
                'data' => [
                    'subscriber_id' => (int) $id,
                ],
            ]);
        }

        $response = Http::withToken($this->accessToken)
            ->post('https://api.manychat.com/fb/subscriber/setCustomFields', [
                'subscriber_id' => (int) $id,
                'fields' => $fields,
            ]);

        return $this->respondFromManyChat($response);
    }

    protected function findSubscriberId(string $email): array
    {
        $response = Http::withHeaders([
            'accept' => 'application/json',
            'Authorization' => 'Bearer '.$this->accessToken,
        ])->get('https://api.manychat.com/fb/subscriber/findBySystemField', [
            'email' => $email,
        ]);

        if (! $response->successful()) {
            return ['error' => $this->respondFromManyChat($response)];
        }

        return ['id' => data_get($response->json(), 'data.id')];
    }

    protected function normalizeSystemFields(array &$data): void
    {
        if (array_key_exists('phone', $data)) {
            $data['phone'] = $this->normalizePhone($data['phone']);
        }

        if (array_key_exists('whatsapp_phone', $data)) {
            $data['whatsapp_phone'] = $this->normalizePhone($data['whatsapp_phone']);
        }
    }

    protected function normalizePhone(?string $phone): string
    {
        $digits = preg_replace('/\D+/', '', (string) $phone);
        if ($digits === '') {
            return '';
        }

        if (str_starts_with($digits, '55')) {
            return $digits;
        }

        if (strlen($digits) === 10 || strlen($digits) === 11) {
            return '55'.$digits;
        }

        return $digits;
    }

    protected function respondFromManyChat($response): JsonResponse
    {
        return response()->json($response->json(), $response->status());
    }
}
