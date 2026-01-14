<?php

namespace App\Data;

use Spatie\LaravelData\Data;
use Spatie\LaravelData\Optional;

class UpdateArticleData extends Data
{
    public function __construct(
        public int $id,
        public string|Optional $title,
        public string|Optional $content,
    ) {}
}
