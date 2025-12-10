<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Article;
use App\Models\Comment;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 先に作成した記事を取得
        $article1 = Article::find(1);
        $article2 = Article::find(2);

        $article1->comments()->create([
            'message' => 'コメント1',
        ]);

        $article1->comments()->create([
            'message' => 'コメント1',
        ]);

        $article2->comments()->create([
            'message' => 'コメント2',
        ]);
    }
}
