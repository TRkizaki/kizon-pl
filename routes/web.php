<?php

use App\Http\Controllers\MatchweekController;
use Illuminate\Support\Facades\Route;

Route::get('/', [MatchweekController::class, 'current'])->name('home');

// Only matchweeks 1–38 exist; anything else never matches, so it is a plain 404.
Route::get('/matchweek/{matchweek}', [MatchweekController::class, 'show'])
    ->whereIn('matchweek', range(1, 38))
    ->name('matchweek');
