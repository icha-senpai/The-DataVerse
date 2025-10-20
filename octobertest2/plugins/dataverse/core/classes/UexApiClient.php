<?php namespace Dataverse\Core\Classes;

use Exception;
use Illuminate\Support\Facades\Log;

class UexApiClient
{
    protected string $base = 'https://api.uexcorp.uk/2.0';
    protected int $delay = 7; // seconds between requests (8.5/min ≈ safe under 10/min)

    /** Fetch a single endpoint and return decoded JSON as array */
    public function fetch(string $endpoint): array
    {
        $url = "{$this->base}/{$endpoint}";
        try {
            $json = @file_get_contents($url);
            if ($json === false) {
                Log::warning("[UEX] Failed to fetch {$url}");
                return [];
            }
            $data = json_decode($json, true);
            if (!is_array($data)) {
                Log::warning("[UEX] Invalid JSON from {$url}");
                return [];
            }
            sleep($this->delay); // respect rate limit
            return $data;
        } catch (Exception $e) {
            Log::error("[UEX] Exception while fetching {$endpoint}: " . $e->getMessage());
            return [];
        }
    }

    /** Handle paginated endpoints */
    public function fetchPaginated(string $endpoint): array
    {
        $page = 1;
        $results = [];
        do {
            $batch = $this->fetch("{$endpoint}?page={$page}");
            if (empty($batch)) break;
            $results = array_merge($results, $batch);
            $page++;
        } while (count($batch) > 0);

        return $results;
    }
}
