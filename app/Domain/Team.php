<?php

namespace App\Domain;

final readonly class Team
{
    public function __construct(
        public int $id,
        public string $name,
        public string $shortName,
        public string $tla,
        public ?string $crest,
    ) {}
}
