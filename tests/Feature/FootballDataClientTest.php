<?php

namespace Tests\Feature;

use App\FootballData\FootballDataClient;
use App\FootballData\FootballDataUnavailable;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FootballDataClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests(); // no test may ever reach the real API
    }

    public function test_it_fetches_the_season_once_then_serves_it_from_the_cache(): void
    {
        Http::fake([
            'api.football-data.org/v4/competitions/PL/matches*' => Http::response($this->fixture('season-2026')),
        ]);

        $client = app(FootballDataClient::class);

        $this->assertCount(380, $client->seasonFixtures());
        $this->assertCount(380, $client->seasonFixtures());

        Http::assertSentCount(1);
        Http::assertSent(fn (Request $request) => $request->url() === 'https://api.football-data.org/v4/competitions/PL/matches?season=2026'
            && $request->hasHeader('X-Auth-Token', 'test-token'));
    }

    public function test_it_reads_the_current_matchweek(): void
    {
        Http::fake([
            'api.football-data.org/v4/competitions/PL' => Http::response($this->fixture('competition')),
        ]);

        $this->assertSame(6, app(FootballDataClient::class)->currentMatchweek());
    }

    public function test_an_api_error_with_nothing_cached_throws(): void
    {
        Http::fake([
            'api.football-data.org/*' => Http::response(['message' => 'Too many requests'], 429),
        ]);

        $this->expectException(FootballDataUnavailable::class);

        app(FootballDataClient::class)->seasonFixtures();
    }

    private function fixture(string $name): array
    {
        return json_decode(file_get_contents(base_path("tests/Fixtures/football-data/{$name}.json")), true);
    }
}
