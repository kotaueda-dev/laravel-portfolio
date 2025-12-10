<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Article;

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
