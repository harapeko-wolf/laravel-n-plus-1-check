<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        echo "コメントデータを生成中...\n";
        
        $faker = Faker::create('ja_JP');
        
        // 投稿IDの範囲を取得
        $postIds = DB::table('posts')->pluck('id')->toArray();
        $totalPosts = count($postIds);
        
        echo "対象投稿数: {$totalPosts}件\n";
        
        $comments = [];
        $totalComments = 0;
        
        // 各投稿に対してランダムな数のコメントを生成
        foreach ($postIds as $index => $postId) {
            $commentCount = $faker->numberBetween(5, 15); // 投稿あたり5-15件のコメント
            
            for ($i = 0; $i < $commentCount; $i++) {
                $comments[] = [
                    'post_id' => $postId,
                    'author_name' => $faker->name,
                    'author_email' => $faker->safeEmail,
                    'content' => $faker->realText($faker->numberBetween(50, 300)),
                    'approved' => $faker->boolean(90), // 90%を承認済み
                    'created_at' => $faker->dateTimeBetween('-1 year', 'now'),
                    'updated_at' => now(),
                ];
                
                $totalComments++;
                
                // メモリ効率のため1000件ずつ挿入
                if (count($comments) >= 1000) {
                    DB::table('comments')->insert($comments);
                    $comments = [];
                    echo "コメント {$totalComments} 件生成完了\n";
                }
            }
            
            // 進捗表示
            if (($index + 1) % 10000 === 0) {
                echo "投稿 " . ($index + 1) . "/{$totalPosts} 処理完了\n";
            }
        }
        
        // 残りがあれば挿入
        if (!empty($comments)) {
            DB::table('comments')->insert($comments);
        }
        
        echo "コメントデータ生成完了: {$totalComments}件\n";
    }
}
