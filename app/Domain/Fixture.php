<?php

namespace App\Domain;

use DateTimeImmutable;

final readonly class Fixture
{
    public function __construct(
        public int $id,
        public int $matchweek,
        public DateTimeImmutable $kickoff, // UTC; converted to UK time only in the view
        public FixtureStatus $status,
        public Team $home,
        public Team $away,
        public ?Score $fullTime, // null until the match has a score
        public ?Score $halfTime,
    ) {}
}
