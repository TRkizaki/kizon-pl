@php
    $first = $digest->firstKickoff()?->setTimezone($timezone);
    $last = $digest->lastKickoff()?->setTimezone($timezone);
@endphp

<x-layout :title="'Premier League · Matchweek '.$digest->matchweek">
    <header>
        <p class="competition">Premier League {{ $season }}</p>
        <h1>Matchweek {{ $digest->matchweek }}</h1>
        @if ($first)
            <p class="dates">
                {{ $first->format('D j M') }}@if ($first->format('Y-m-d') !== $last->format('Y-m-d')) – {{ $last->format('D j M') }}@endif
            </p>
        @endif
    </header>

    <ol class="results">
        @foreach ($digest->fixtures as $fixture)
            <li class="fixture status-{{ strtolower($fixture->status->value) }}">
                <span class="team home">{{ $fixture->home->shortName }}</span>
                <span class="score">{{ $fixture->fullTime ? $fixture->fullTime->home.'–'.$fixture->fullTime->away : '–' }}</span>
                <span class="team away">{{ $fixture->away->shortName }}</span>
                <span class="meta">
                    @if ($fixture->status->isLive())
                        live
                    @elseif ($fixture->status->counts())
                        {{ $fixture->halfTime ? 'HT '.$fixture->halfTime->home.'–'.$fixture->halfTime->away : '' }}
                    @elseif ($fixture->status->isCalledOff())
                        {{ strtolower($fixture->status->name) }}
                    @else
                        {{ $fixture->kickoff->setTimezone($timezone)->format('D H:i') }}
                    @endif
                </span>
            </li>
        @endforeach
    </ol>
</x-layout>
