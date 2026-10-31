<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class BeemService
{
    public function send(array $payload): array
    {
        $apiKey = config('services.beem.key');
        $secret = config('services.beem.secret');
        $endpoint = config('services.beem.url');

        if (! $apiKey || ! $secret || ! $endpoint) {
            return ['success' => false, 'message' => 'BEEM credentials are not configured.'];
        }

        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'X-API-KEY' => $apiKey,
            'X-API-SECRET' => $secret,
        ])->post($endpoint, $payload);

        if ($response->failed()) {
            return ['success' => false, 'message' => $response->body() ?: 'BEEM request failed'];
        }

        return ['success' => true, 'data' => $response->json()];
    }
}
