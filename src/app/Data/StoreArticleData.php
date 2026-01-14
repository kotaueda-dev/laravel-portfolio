<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class StoreArticleData extends Data
{
    public function __construct(
        public string $title,
        public string $content,
        public int $user_id,
    ) {}
}
