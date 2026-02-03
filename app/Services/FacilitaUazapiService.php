<?php

namespace App\Services;

use App\Models\WebhookEndpoint;
use App\Services\Contracts\WebhookHandlerInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Http;

class FacilitaUazapiService implements WebhookHandlerInterface
{
    public function handle(WebhookEndpoint $endpoint, array $payload): JsonResponse
    {
        $phone = $this->normalizePhone($payload['phone'] ?? null);
        $prompt = $this->stringValue($payload['prompt'] ?? null);
        $token = $this->stringValue($payload['token'] ?? null);

        if ($phone === '') {
            return response()->json(['message' => 'Campo phone obrigatorio.'], 422);
        }

        if ($prompt === '') {
            return response()->json(['message' => 'Campo prompt obrigatorio.'], 422);
        }

        if ($token === '') {
            return response()->json(['message' => 'Campo token obrigatorio.'], 422);
        }

        $data = [
            'message' => [
                'chatid' => $phone."@s.whatsapp.net",
                'text' => $prompt,
            ],
            'token' => $token,
        ];

        $response = Http::post('https://facilitaiagencia.3f7.org/api/uazapi/messages/text', $data);

        return response()->json($response->json(), $response->status());
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
