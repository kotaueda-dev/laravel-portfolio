<?php

namespace Database\Seeders;

use App\Models\Article;
use App\Models\Comment;
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

        // Create 50 articles
        Article::factory(50)->create(['user_id' => $user->id])->each(function ($article) {
            // Create 5 comments for each article
            Comment::factory(5)->create(['article_id' => $article->id]);
        });
    }
}
