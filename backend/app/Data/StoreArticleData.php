<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class StoreArticleData extends Data
{
    public function __construct(
        public readonly string $title,
        public readonly string $content,
        public readonly int $user_id,
    ) {}
}
