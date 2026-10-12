<?php

namespace App\FootballData;

use App\Domain\Fixture;
use App\Domain\FixtureStatus;
use App\Domain\Score;
use App\Domain\Team;
use DateTimeImmutable;

/**
 * Turns football-data.org JSON into domain objects. This is the only class that
 * knows the API's field names, including that it says "matchday" where the rest
 * of the app says "matchweek".
 */
class FixtureMapper
{
    /**
     * @return list<Fixture>
     */
    public function fixtures(array $response): array
    {
        return array_map($this->fixture(...), $response['matches']);
    }

    public function fixture(array $match): Fixture
    {
        return new Fixture(
            id: $match['id'],
            matchweek: $match['matchday'],
            kickoff: new DateTimeImmutable($match['utcDate']),
            status: FixtureStatus::from($match['status']),
            home: $this->team($match['homeTeam']),
            away: $this->team($match['awayTeam']),
            fullTime: $this->score($match['score']['fullTime']),
            halfTime: $this->score($match['score']['halfTime']),
        );
    }

    private function team(array $team): Team
    {
        return new Team(
            id: $team['id'],
            name: $team['name'],
            shortName: $team['shortName'],
            tla: $team['tla'],
            crest: $team['crest'] ?? null,
        );
    }

    private function score(array $score): ?Score
    {
        if ($score['home'] === null || $score['away'] === null) {
            return null;
        }

        return new Score($score['home'], $score['away']);
    }
}
