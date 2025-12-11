<?php

namespace Database\Seeders;

use App\Models\Article;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Article::create([
            'title' => '記事1',
            'content' => '本文',
            'username' => 'ユーザー',
        ]);

        Article::create([
            'title' => '記事2',
            'content' => '本文',
            'username' => 'ユーザー',
        ]);

        Article::create([
            'title' => '記事3',
            'content' => '本文',
            'username' => 'ユーザー',
        ]);
    }
}
