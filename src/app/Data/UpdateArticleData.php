<?php

namespace App\Data;

use Spatie\LaravelData\Data;

class UpdateArticleData extends Data
{
    public function __construct(
        public int $id,
        public string $title = '',
        public string $content = '',
    ) {}
}
