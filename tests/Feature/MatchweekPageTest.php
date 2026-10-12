<?php

namespace Tests\Feature;

use App\FootballData\FootballDataUnavailable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Exceptions;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MatchweekPageTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::preventStrayRequests(); // no test may ever reach the real API

        // A Wednesday with no fixtures, so the season's six-hour "idle" cache window applies.
        $this->travelTo(Carbon::parse('2026-10-14 12:00:00', 'UTC'));
    }

    public function test_the_home_page_shows_the_current_matchweek(): void
    {
        $this->fakeApi();

        $this->get('/')
            ->assertOk()
            ->assertSee('Matchweek 6')
            ->assertSee('Sat 10 Oct – Mon 12 Oct')
            ->assertSeeInOrder(['Arsenal', '2–1', 'Leeds United', 'HT 0–0'])
            ->assertSee('football-data.org');
    }

    public function test_a_past_matchweek_has_its_own_page(): void
    {
        $this->fakeApi();

        $this->get('/matchweek/1')
            ->assertOk()
            ->assertSeeInOrder(['Arsenal', '3–0', 'Coventry City', 'HT 2–0']);
    }

    public function test_an_upcoming_fixture_shows_its_uk_kick_off_time(): void
    {
        $this->fakeApi();

        // The final day kicks off at 15:00 UTC on 30 May, which is 16:00 in British Summer Time.
        $this->get('/matchweek/38')
            ->assertOk()
            ->assertSee('Sun 16:00');
    }

    public function test_matchweeks_outside_1_to_38_do_not_exist(): void
    {
        $this->get('/matchweek/39')->assertNotFound();
        $this->get('/matchweek/0')->assertNotFound();
        $this->get('/matchweek/abc')->assertNotFound();
    }

    public function test_a_stale_copy_is_served_when_the_api_fails(): void
    {
        Exceptions::fake();
        $this->fakeApiThatFailsAfterTheFirstCall();

        $this->get('/')->assertOk(); // warms the cache: two API calls

        $this->travel(7)->hours(); // past the 6 h fresh window, still inside the 24 h stale window

        $this->get('/')
            ->assertOk()
            ->assertSee('Matchweek 6');

        // Both refreshes ran after the response, failed, and were reported rather than breaking the page.
        Http::assertSentCount(4);
        Exceptions::assertReported(FootballDataUnavailable::class);
    }

    public function test_the_api_down_with_nothing_cached_shows_the_503_page(): void
    {
        Http::fake(['api.football-data.org/*' => Http::response(['message' => 'Server error'], 500)]);

        $this->get('/')
            ->assertStatus(503)
            ->assertSee('Data temporarily unavailable');
    }

    /**
     * Serve the recorded fixtures on every call.
     */
    private function fakeApi(): void
    {
        Http::fake([
            'api.football-data.org/v4/competitions/PL/matches*' => Http::response($this->fixture('season-2026')),
            'api.football-data.org/v4/competitions/PL' => Http::response($this->fixture('competition')),
        ]);
    }

    /**
     * Serve the recorded fixtures once, then answer every later call with HTTP 500.
     */
    private function fakeApiThatFailsAfterTheFirstCall(): void
    {
        $error = Http::response(['message' => 'Server error'], 500);

        Http::fake([
            'api.football-data.org/v4/competitions/PL/matches*' => Http::sequence()->push($this->fixture('season-2026'))->whenEmpty($error),
            'api.football-data.org/v4/competitions/PL' => Http::sequence()->push($this->fixture('competition'))->whenEmpty($error),
        ]);
    }

    private function fixture(string $name): array
    {
        return json_decode(file_get_contents(base_path("tests/Fixtures/football-data/{$name}.json")), true);
    }
}
