<?php

namespace App\Helpers;

class CacheKeyHelper
{
    public static function articleListTag(): string
    {
        return 'articles:list:page';
    }

    public static function articleListKey(string $page): string
    {
        return "articles:list:page:{$page}";
    }

    public static function articleDetailKey(string $id): string
    {
        return "articles:detail:{$id}";
    }
}
