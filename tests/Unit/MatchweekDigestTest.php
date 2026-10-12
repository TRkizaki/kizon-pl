<?php

namespace Tests\Unit;

use App\Domain\MatchweekDigest;
use PHPUnit\Framework\TestCase;

class MatchweekDigestTest extends TestCase
{
    public function test_an_empty_matchweek_has_no_kick_off_range(): void
    {
        $digest = new MatchweekDigest(39, []);

        $this->assertNull($digest->firstKickoff());
        $this->assertNull($digest->lastKickoff());
    }
}
