<?php

namespace App\Domain;

/**
 * Match states, using football-data.org's values.
 */
enum FixtureStatus: string
{
    case Scheduled = 'SCHEDULED';
    case Timed = 'TIMED';
    case InPlay = 'IN_PLAY';
    case Paused = 'PAUSED';
    case Finished = 'FINISHED';
    case Awarded = 'AWARDED';
    case Suspended = 'SUSPENDED';
    case Postponed = 'POSTPONED';
    case Cancelled = 'CANCELLED';

    /**
     * Whether the result counts towards the league table.
     */
    public function counts(): bool
    {
        return $this === self::Finished || $this === self::Awarded;
    }

    /**
     * Whether the match is being played right now (half-time included).
     */
    public function isLive(): bool
    {
        return $this === self::InPlay || $this === self::Paused;
    }
}
