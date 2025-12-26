<?php

namespace App\Services\Contracts;

use App\Models\WebhookEndpoint;
use Illuminate\Http\JsonResponse;

interface WebhookHandlerInterface
{
    public function handle(WebhookEndpoint $endpoint, array $payload): JsonResponse;
}
