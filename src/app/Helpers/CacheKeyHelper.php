<?php

namespace App\Helpers;

class CacheKeyHelper
{
    public static function articleListTag()
    {
        return 'articles:list:page';
    }

    public static function articleListKey(string $page)
    {
        return "articles:list:page:{$page}";
    }

    public static function articleDetailKey(string $id)
    {
        return "articles:detail:{$id}";
    }
}
