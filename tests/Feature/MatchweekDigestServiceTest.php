<?php

namespace Tests\Feature;

use App\Domain\Fixture;
use App\Domain\FixtureStatus;
use App\Services\MatchweekDigestService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MatchweekDigestServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests();
        Http::fake([
            'api.football-data.org/v4/competitions/PL/matches*' => Http::response($this->fixture('season-2026')),
            'api.football-data.org/v4/competitions/PL' => Http::response($this->fixture('competition')),
        ]);
    }

    public function test_the_current_matchweek_comes_in_kick_off_order(): void
    {
        $digest = app(MatchweekDigestService::class)->current();

        $this->assertSame(6, $digest->matchweek);
        $this->assertCount(10, $digest->fixtures);
        $this->assertSame(560593, $digest->fixtures[0]->id); // Arsenal v Leeds United, Saturday 11:30 UTC
        $this->assertSame(560597, array_last($digest->fixtures)->id); // Coventry City v Newcastle, Monday night
        $this->assertSame('2026-10-10 11:30', $digest->firstKickoff()->format('Y-m-d H:i'));
        $this->assertSame('2026-10-12 19:00', $digest->lastKickoff()->format('Y-m-d H:i'));
    }

    public function test_fixtures_that_kick_off_together_are_ordered_by_id(): void
    {
        $digest = app(MatchweekDigestService::class)->forMatchweek(38); // the final day: all ten at 15:00 UTC

        $ids = array_map(fn (Fixture $fixture) => $fixture->id, $digest->fixtures);
        $sorted = $ids;
        sort($sorted);

        $this->assertCount(10, $ids);
        $this->assertSame($sorted, $ids);
    }

    public function test_a_finished_matchweek_has_every_result(): void
    {
        $digest = app(MatchweekDigestService::class)->forMatchweek(1);

        $this->assertSame(560542, $digest->fixtures[0]->id); // the opener: Arsenal 3–0 Coventry City

        foreach ($digest->fixtures as $fixture) {
            $this->assertSame(FixtureStatus::Finished, $fixture->status);
            $this->assertNotNull($fixture->fullTime);
            $this->assertNotNull($fixture->halfTime);
        }
    }

    private function fixture(string $name): array
    {
        return json_decode(file_get_contents(base_path("tests/Fixtures/football-data/{$name}.json")), true);
    }
}
