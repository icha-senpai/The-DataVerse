<?php namespace Dataverse\Core\Classes;

use Exception;
use Illuminate\Support\Facades\Log;

/**
 * Handles API requests to UEX 2.0 and provides simple rate-limited fetching.
 */
class UexApiClient
{
    /** Base URL for all requests */
    protected string $base = 'https://api.uexcorp.uk/2.0';

    /** Delay between API requests (seconds) */
    protected int $delay = 7;

    /** Timeout for stream context (seconds) */
    protected int $timeout = 15;

    /**
     * Fetch a single endpoint and return decoded JSON as array.
     */
    public function fetch(string $endpoint): array
    {
        $url = "{$this->base}/{$endpoint}";
        Log::info("[UEX] Fetching {$url}");

        try {
            $context = stream_context_create([
                'http' => [
                    'timeout' => $this->timeout,
                    'ignore_errors' => true,
                    'header' => [
                        'User-Agent: Dataverse-Core/1.0 (+https://ichaa.net)',
                        'Accept: application/json'
                    ]
                ]
            ]);

            $json = @file_get_contents($url, false, $context);

            if ($json === false) {
                Log::warning("[UEX] Failed to fetch {$url}");
                return [];
            }

            $data = json_decode($json, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::warning("[UEX] JSON decode error from {$url}: " . json_last_error_msg());
                return [];
            }

            if (!is_array($data)) {
                Log::warning("[UEX] Invalid response format from {$url}");
                return [];
            }

            sleep($this->delay); // obey rate limit
            return $data;
        } catch (Exception $e) {
            Log::error("[UEX] Exception while fetching {$url}: {$e->getMessage()}");
            return [];
        }
    }

    /**
     * Handle paginated endpoints.
     * Combines results across pages until an empty set is returned.
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
