<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\User;
use Illuminate\Database\Seeder;

class ArticleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::where('email', 'default@example.com')->first();

        Article::create([
            'title' => '記事1',
            'content' => '本文',
            'user_id' => $user->id,
        ]);

        Article::create([
            'title' => '記事2',
            'content' => '本文',
            'user_id' => $user->id,
        ]);

        Article::create([
            'title' => '記事3',
            'content' => '本文',
            'user_id' => $user->id,
        ]);
    }
}
