<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        echo "=== Laravel N+1 クエリ問題デモ用データ生成開始 ===\n";
        echo "想定時間: 3-5分\n\n";
        
        $this->call([
            AuthorSeeder::class,
            PostSeeder::class,
            CommentSeeder::class,
        ]);
        
        echo "\n=== データ生成完了 ===\n";
        echo "生成されたデータ:\n";
        echo "- 著者: 100件\n";
        echo "- 投稿: 100,000件\n";
        echo "- コメント: 約500,000-1,500,000件\n";
        echo "\nデモページにアクセスしてN+1クエリ問題を体感してください！\n";
    }
}
