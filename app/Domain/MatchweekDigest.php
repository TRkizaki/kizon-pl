<?php

namespace App\Domain;

use DateTimeImmutable;

/**
 * One matchweek, ready to show: its fixtures in kick-off order.
 * The table and movement join it in later steps.
 */
final readonly class MatchweekDigest
{
    /**
     * @param  list<Fixture>  $fixtures  in kick-off order
     */
    public function __construct(
        public int $matchweek,
        public array $fixtures,
    ) {}

    public function firstKickoff(): ?DateTimeImmutable
    {
        return array_first($this->fixtures)?->kickoff;
    }

    public function lastKickoff(): ?DateTimeImmutable
    {
        return array_last($this->fixtures)?->kickoff;
    }
}
