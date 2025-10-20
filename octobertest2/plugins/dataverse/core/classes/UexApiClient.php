<?php namespace Dataverse\Core\Classes;

use Exception;
use Illuminate\Support\Facades\Log;

class UexApiClient
{
    /** Base URL for the API */
    protected string $base = 'https://api.uexcorp.uk/2.0';

    /** Delay between requests (seconds) */
    protected int $delay = 7;

    /** Timeout for HTTP requests */
    protected int $timeout = 15;

    /**
     * Fetch a single endpoint and return decoded JSON array.
     */
    public function fetch(string $endpoint): array
    {
        $url = "{$this->base}/{$endpoint}";
        $token = env('UEX_API_TOKEN');

        // Add authentication header if token exists
        $headers = [
            'Accept: application/json',
            'User-Agent: Dataverse-Core/1.0 (+https://ichaa.net)',
        ];

        if (!empty($token)) {
            $headers[] = "Authorization: Bearer {$token}";
        }

        $context = stream_context_create([
            'http' => [
                'timeout' => $this->timeout,
                'ignore_errors' => true,
                'header' => $headers
            ]
        ]);

        try {
            $json = @file_get_contents($url, false, $context);

            if ($json === false) {
                Log::warning("[UEX] Failed to fetch {$url}");
                return [];
            }

            $data = json_decode($json, true);

            // Handle wrapped responses like { status, data }
            if (is_array($data)) {
                if (isset($data['status']) && strtolower($data['status']) === 'ok') {
                    if (isset($data['data']) && is_array($data['data'])) {
                        sleep($this->delay);
                        return $data['data'];
                    }
                    // Empty OK response
                    Log::warning("[UEX] OK response but no data at {$url}");
                    return [];
                }

                // Error message from API
                if (isset($data['status']) && strtolower($data['status']) !== 'ok') {
                    $msg = $data['message'] ?? 'unknown error';
                    Log::warning("[UEX] API error at {$url}: {$msg}");
                    return [];
                }

                // Maybe response is direct array (no wrapper)
                if (array_is_list($data) && count($data) > 0) {
                    sleep($this->delay);
                    return $data;
                }

                Log::warning("[UEX] Unexpected response structure at {$url}: " . substr($json, 0, 200));
                return [];
            }

            Log::warning("[UEX] Invalid JSON from {$url}");
            return [];
        } catch (Exception $e) {
            Log::error("[UEX] Exception fetching {$endpoint}: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Handle paginated endpoints (fetch all pages)
     */
    public function fetchPaginated(string $endpoint): array
    {
        $page = 1;
        $results = [];

        while (true) {
            $batch = $this->fetch("{$endpoint}?page={$page}");
            if (empty($batch)) {
                Log::info("[UEX] No more data after page {$page} for {$endpoint}");
                break;
            }

            $results = array_merge($results, $batch);
            Log::info("[UEX] Page {$page} fetched: " . count($batch) . " items");
            $page++;
        }

        return $results;
    }
}
