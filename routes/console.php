<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('fixtures:record {--offline : Re-trim the existing files instead of calling the API}', function () {
    $competition = config('football.competition');
    $season = config('football.season');
    $team = fn (array $team) => Arr::only($team, ['id', 'name', 'shortName', 'tla']);

    // Each fixture keeps the API's shape but only the fields the app reads,
    // so the committed files hold plain match facts and nothing more.
    $fixtures = [
        'competition' => ["/competitions/{$competition}", fn (array $response) => [
            'code' => $response['code'],
            'name' => $response['name'],
            'currentSeason' => Arr::only($response['currentSeason'], ['startDate', 'endDate', 'currentMatchday']),
        ]],
        "season-{$season}" => ["/competitions/{$competition}/matches?season={$season}", fn (array $response) => [
            'matches' => array_map(fn (array $match) => [
                ...Arr::only($match, ['id', 'utcDate', 'status', 'matchday']),
                'homeTeam' => $team($match['homeTeam']),
                'awayTeam' => $team($match['awayTeam']),
                'score' => Arr::only($match['score'], ['fullTime', 'halfTime']),
            ], $response['matches']),
        ]],
        'standings' => ["/competitions/{$competition}/standings", fn (array $response) => [
            'standings' => array_map(fn (array $standing) => [
                'type' => $standing['type'],
                'table' => array_map(fn (array $row) => [
                    ...Arr::only($row, ['position', 'playedGames', 'won', 'draw', 'lost', 'points', 'goalsFor', 'goalsAgainst', 'goalDifference']),
                    'team' => $team($row['team']),
                ], $standing['table']),
            ], $response['standings']),
        ]],
    ];

    foreach ($fixtures as $name => [$path, $trim]) {
        $file = base_path("tests/Fixtures/football-data/{$name}.json");

        $response = $this->option('offline')
            ? json_decode(File::get($file), true)
            : Http::baseUrl(config('services.football_data.base_url'))
                ->withHeaders(['X-Auth-Token' => config('services.football_data.token')])
                ->get($path)
                ->throw()
                ->json();

        $json = json_encode($trim($response), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n";
        File::put($file, $json);

        $this->info("{$name}.json: ".number_format(strlen($json)).' bytes');
    }
})->purpose('Record the football-data.org test fixtures, keeping only the fields the app reads');
