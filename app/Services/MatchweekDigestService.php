<?php

namespace App\Services;

use App\Domain\Fixture;
use App\Domain\MatchweekDigest;
use App\FootballData\FootballDataClient;

/**
 * Builds the digest for one matchweek. Knows nothing about HTTP: the controller
 * decides which matchweek to ask for and how to show it.
 */
class MatchweekDigestService
{
    public function __construct(
        private readonly FootballDataClient $client,
    ) {}

    public function current(): MatchweekDigest
    {
        return $this->forMatchweek($this->client->currentMatchweek());
    }

    public function forMatchweek(int $matchweek): MatchweekDigest
    {
        $fixtures = array_values(array_filter(
            $this->client->seasonFixtures(),
            fn (Fixture $fixture) => $fixture->matchweek === $matchweek,
        ));

        // Kick-off order; fixtures that start together are ordered by id so the page never reshuffles.
        usort($fixtures, fn (Fixture $a, Fixture $b) => [$a->kickoff, $a->id] <=> [$b->kickoff, $b->id]);

        return new MatchweekDigest($matchweek, $fixtures);
    }
}
