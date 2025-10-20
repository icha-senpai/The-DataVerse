<?php namespace Dataverse\Core\Classes;

use Exception;
use Illuminate\Support\Facades\Log;

class UexApiClient
{
    protected string $base = 'https://api.uexcorp.uk/2.0';
    protected int $delay = 7; // seconds between requests (safe under 10/min)

    protected function request(string $endpoint): ?string
    {
        $url = "{$this->base}/{$endpoint}";
        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => "Authorization: Bearer " . env('UEX_API_TOKEN') . "\r\n" .
                            "Accept: application/json\r\n",
                'timeout' => 20,
            ]
        ];
        $ctx = stream_context_create($opts);
        return @file_get_contents($url, false, $ctx) ?: null;
    }

    public function fetch(string $endpoint): array
    {
        try {
            $json = $this->request($endpoint);
            if ($json === null) {
                Log::warning("[UEX] Fetch failed: {$endpoint}");
                return [];
            }

            $data = json_decode($json, true);
            if (!is_array($data)) {
                Log::warning("[UEX] Invalid JSON from {$endpoint}");
                return [];
            }

            // unwrap if wrapped under 'data'
            if (isset($data['data']) && is_array($data['data'])) {
                $data = $data['data'];
            }

            sleep($this->delay);
            return $data;
        } catch (Exception $e) {
            Log::error("[UEX] Exception while fetching {$endpoint}: {$e->getMessage()}");
            return [];
        }
    }
}
