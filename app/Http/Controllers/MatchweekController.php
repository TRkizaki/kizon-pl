<?php

namespace App\Http\Controllers;

use App\Domain\MatchweekDigest;
use App\Services\MatchweekDigestService;
use DateTimeZone;
use Illuminate\View\View;

class MatchweekController extends Controller
{
    public function __construct(
        private readonly MatchweekDigestService $digests,
    ) {}

    public function current(): View
    {
        return $this->page($this->digests->current());
    }

    public function show(int $matchweek): View
    {
        return $this->page($this->digests->forMatchweek($matchweek));
    }

    private function page(MatchweekDigest $digest): View
    {
        $season = config('football.season');

        return view('matchweek', [
            'digest' => $digest,
            'season' => sprintf('%d/%02d', $season, ($season + 1) % 100), // 2026 → "2026/27"
            'timezone' => new DateTimeZone(config('football.display_timezone')),
        ]);
    }
}
