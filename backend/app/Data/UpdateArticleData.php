<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class UpdateArticleData extends Data
{
    public function __construct(
        public readonly int $id,
        public readonly string|Optional $title,
        public readonly string|Optional $content,
    ) {}
}
