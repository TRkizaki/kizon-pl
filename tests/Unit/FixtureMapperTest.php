<?php

namespace Tests\Unit;

use App\Domain\Fixture;
use App\Domain\FixtureStatus;
use App\FootballData\FixtureMapper;
use PHPUnit\Framework\TestCase;

class FixtureMapperTest extends TestCase
{
    public function test_it_maps_every_fixture_of_the_season(): void
    {
        $fixtures = (new FixtureMapper)->fixtures($this->season());

        $this->assertCount(380, $fixtures);
        $this->assertContainsOnlyInstancesOf(Fixture::class, $fixtures);
    }

    public function test_it_maps_a_finished_match(): void
    {
        $opener = $this->fixture(560542); // Arsenal 3–0 Coventry City, 21 Aug 2026

        $this->assertSame(1, $opener->matchweek);
        $this->assertSame(FixtureStatus::Finished, $opener->status);
        $this->assertSame('2026-08-21 19:00 +00:00', $opener->kickoff->format('Y-m-d H:i P'));
        $this->assertSame('Arsenal', $opener->home->shortName);
        $this->assertSame('COV', $opener->away->tla);
        $this->assertSame([3, 0], [$opener->fullTime->home, $opener->fullTime->away]);
        $this->assertSame([2, 0], [$opener->halfTime->home, $opener->halfTime->away]);
    }

    public function test_a_match_not_yet_played_has_no_scores(): void
    {
        $fixture = $this->fixture(560597); // Coventry City v Newcastle, Monday night of matchweek 6

        $this->assertSame(FixtureStatus::Timed, $fixture->status);
        $this->assertSame(6, $fixture->matchweek);
        $this->assertNull($fixture->fullTime);
        $this->assertNull($fixture->halfTime);
    }

    private function season(): array
    {
        return json_decode(file_get_contents(__DIR__.'/../Fixtures/football-data/season-2026.json'), true);
    }

    private function fixture(int $id): Fixture
    {
        $match = array_find($this->season()['matches'], fn (array $match) => $match['id'] === $id);

        return (new FixtureMapper)->fixture($match);
    }
}
