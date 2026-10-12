<?php

namespace App\FootballData;

use App\Domain\Fixture;
use Illuminate\Container\Attributes\Config;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Reads football-data.org through the cache. Visitors never reach the API:
 * only a cache refresh does. If a refresh fails, the stale copy keeps being
 * served for up to a day.
 */
class FootballDataClient
{
    public function __construct(
        #[Config('services.football_data.token', '')] private readonly string $token,
        #[Config('services.football_data.base_url')] private readonly string $baseUrl,
        #[Config('football.competition')] private readonly string $competition,
        #[Config('football.season')] private readonly int $season,
        #[Config('football.cache')] private readonly array $ttl,
        private readonly FixtureMapper $mapper,
    ) {}

    /**
     * Every fixture of the season, from a single API call.
     *
     * @return list<Fixture>
     */
    public function seasonFixtures(): array
    {
        $key = "football-data:{$this->competition}:matches:{$this->season}";

        $response = Cache::flexible(
            $key,
            [$this->freshSeconds(Cache::get($key)), $this->ttl['stale']],
            fn () => $this->get("/competitions/{$this->competition}/matches", ['season' => $this->season]),
        );

        return $this->mapper->fixtures($response);
    }

    /**
     * The matchweek the competition is on now.
     */
    public function currentMatchweek(): int
    {
        $response = Cache::flexible(
            "football-data:{$this->competition}:competition",
            [$this->ttl['competition'], $this->ttl['stale']],
            fn () => $this->get("/competitions/{$this->competition}"),
        );

        return $response['currentSeason']['currentMatchday'];
    }

    /**
     * GET from the API, or throw FootballDataUnavailable. Retries once on a
     * connection error only, never on an HTTP error such as 429.
     */
    private function get(string $path, array $query = []): array
    {
        try {
            $response = Http::baseUrl($this->baseUrl)
                ->withHeaders(['X-Auth-Token' => $this->token])
                ->acceptJson()
                ->timeout(5)
                ->retry(2, 250, fn ($exception) => $exception instanceof ConnectionException, throw: false)
                ->get($path, $query);
        } catch (ConnectionException $e) {
            throw new FootballDataUnavailable("football-data.org unreachable: {$e->getMessage()}", previous: $e);
        }

        Log::info('football-data.org request', [
            'path' => $path,
            'status' => $response->status(),
            'requests_left_this_minute' => $response->header('X-Requests-Available-Minute'),
            'counter_resets_in_seconds' => $response->header('X-RequestCounter-Reset'),
        ]);

        if ($response->failed()) {
            throw new FootballDataUnavailable("football-data.org returned HTTP {$response->status()} for {$path}");
        }

        return $response->json();
    }

    /**
     * How long the cached season stays fresh, judged from the cached data itself:
     * a minute while a match is live, ten minutes on a matchday, otherwise six hours.
     */
    private function freshSeconds(?array $cached): int
    {
        $matches = $cached['matches'] ?? [];

        if (array_any($matches, fn (array $match) => in_array($match['status'], ['IN_PLAY', 'PAUSED'], true))) {
            return $this->ttl['live'];
        }

        $today = now('UTC')->toDateString();

        if (array_any($matches, fn (array $match) => str_starts_with($match['utcDate'], $today))) {
            return $this->ttl['matchday'];
        }

        return $this->ttl['idle'];
    }
}
