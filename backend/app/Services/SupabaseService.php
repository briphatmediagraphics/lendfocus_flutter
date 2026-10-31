<?php

namespace App\Services;

class SupabaseService
{
    private ?string $url = null;
    private ?string $key = null;

    public function __construct()
    {
        $this->url = env('SUPABASE_URL', env('VITE_SUPABASE_URL'));
        $this->key = env('SUPABASE_SERVICE_ROLE_KEY', env('VITE_SUPABASE_PUBLISHABLE_KEY'));
    }

    public function isConfigured(): bool
    {
        return ! empty($this->url) && ! empty($this->key);
    }

    public function fetch(string $table, array $query = []): array
    {
        $table = $this->mapTable($table);
        $url = $this->buildUrl($table, $query ?: ['select' => '*']);
        $response = $this->request('GET', $url);

        return is_array($response) ? $response : [];
    }

    public function insert(string $table, array $data): ?array
    {
        $table = $this->mapTable($table);
        $url = $this->buildUrl($table, ['return' => 'representation']);

        return $this->request('POST', $url, $data, [
            'Prefer: return=representation',
        ]);
    }

    public function upsert(string $table, array $data, string $conflict = 'id'): ?array
    {
        $table = $this->mapTable($table);
        $url = $this->buildUrl($table, [
            'return' => 'representation',
            'on_conflict' => $conflict,
        ]);

        return $this->request('POST', $url, $data, [
            'Prefer: return=representation, resolution=merge-duplicates',
        ]);
    }

    public function update(string $table, array $data, string $match): ?array
    {
        $table = $this->mapTable($table);
        $url = $this->appendMatch($this->buildUrl($table, ['return' => 'representation']), $match);

        return $this->request('PATCH', $url, $data, [
            'Prefer: return=representation',
        ]);
    }

    public function delete(string $table, string $match): bool
    {
        $table = $this->mapTable($table);
        $url = $this->appendMatch($this->buildUrl($table), $match);
        $response = $this->request('DELETE', $url);

        return $response !== null;
    }

    private function mapTable(string $table): string
    {
        $mapping = [
            'properties' => 'properties',
            'calendar_events' => 'events',
            'appointments' => 'appointments',
            'documents' => 'documents',
            'inquiries' => 'inquiries',
            'tasks' => 'tasks',
            'workers' => 'workers',
            'contracts' => 'contracts',
            'system_notifications' => 'notifications',
            'notifications' => 'notifications',
            'profiles' => 'profiles',
        ];

        return $mapping[$table] ?? $table;
    }

    private function appendMatch(string $url, string $match): string
    {
        return str_contains($url, '?') ? $url . '&' . $match : $url . '?' . $match;
    }

    private function buildUrl(string $table, array $query = []): string
    {
        $queryString = http_build_query($query, '', '&');

        return rtrim($this->url, '/') . '/rest/v1/' . $table . ($queryString ? '?' . $queryString : '');
    }

    private function request(string $method, string $url, array $data = [], array $extraHeaders = [])
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

        $headers = [
            'apikey: ' . $this->key,
            'Authorization: Bearer ' . $this->key,
            'Content-Type: application/json',
        ];

        if (! empty($extraHeaders)) {
            $headers = array_merge($headers, $extraHeaders);
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        if (in_array($method, ['POST', 'PATCH', 'PUT'], true) && ! empty($data)) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($curl);
        $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($response === false || $status >= 400) {
            return null;
        }

        $decoded = json_decode($response, true);

        return $decoded === null ? $response : $decoded;
    }
}
