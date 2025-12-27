<?php

namespace App\Services;

use App\Models\WebhookEndpoint;
use App\Services\Contracts\WebhookHandlerInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class FacilitaiService implements WebhookHandlerInterface
{
    public function handle(WebhookEndpoint $endpoint, array $payload): JsonResponse
    {
        $instance = $this->stringValue($payload['instance'] ?? null);
        $whatsapp = $this->normalizeWhatsapp($payload['whatsapp'] ?? null);
        $prompt = $this->stringValue($payload['prompt'] ?? null);

        if ($instance === '') {
            return response()->json(['message' => 'Campo instance obrigatorio.'], 422);
        }

        if ($whatsapp === '') {
            return response()->json(['message' => 'Campo whatsapp obrigatorio.'], 422);
        }

        if ($prompt === '') {
            return response()->json(['message' => 'Campo prompt obrigatorio.'], 422);
        }

        $data = [
            'instance' => $instance,
            'data' => [
                'key' => [
                    'remoteJid' => $whatsapp.'@s.whatsapp.net',
                    'fromMe' => false,
                ],
                'message' => [
                    'conversation' => $prompt,
                ],
                'messageType' => 'conversation',
            ],
        ];

        $pushName = $this->stringValue($payload['pushName'] ?? null);
        if ($pushName !== '') {
            $data['data']['pushName'] = $pushName;
        }

        $response = Http::post('https://app.3f7.org/api/conversation', $data);

        return response()->json($response->json(), $response->status());
    }

    protected function normalizeWhatsapp(?string $phone): string
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

    protected function stringValue($value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_scalar($value)) {
            return trim((string) $value);
        }

        return trim((string) json_encode($value));
    }
}
