<?php

namespace App\Helpers;

class CacheKeyHelper
{
    public static function articleListTag(): string
    {
        return 'articles:list:page';
    }

    public static function articleListKey(int $page): string
    {
        return "articles:list:page:{$page}";
    }

    public static function articleDetailKey(int $id): string
    {
        return "articles:detail:{$id}";
    }
}
