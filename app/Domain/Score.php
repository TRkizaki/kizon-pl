<?php

namespace App\Domain;

final readonly class Score
{
    public function __construct(
        public int $home,
        public int $away,
    ) {}
}
