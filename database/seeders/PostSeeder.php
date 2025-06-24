<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Faker\Factory as Faker;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        echo "投稿データを生成中...\n";
        
        $faker = Faker::create('ja_JP');
        $posts = [];
        $authorIds = range(1, 100); // 1-100の著者ID
        
        // 100,000件の投稿を生成
        for ($i = 1; $i <= 100000; $i++) {
            $title = $faker->sentence(6);
            $posts[] = [
                'author_id' => $faker->randomElement($authorIds),
                'title' => $title,
                'slug' => Str::slug($title) . '-' . $i,
                'body' => $faker->realText(1000),
                'published' => $faker->boolean(95), // 95%を公開状態
                'created_at' => $faker->dateTimeBetween('-2 years', 'now'),
                'updated_at' => now(),
            ];
            
            // メモリ効率のため1000件ずつ挿入
            if ($i % 1000 === 0) {
                DB::table('posts')->insert($posts);
                $posts = [];
                echo "投稿 {$i} 件生成完了\n";
            }
        }
        
        // 残りがあれば挿入
        if (!empty($posts)) {
            DB::table('posts')->insert($posts);
        }
        
        echo "投稿データ生成完了: 100,000件\n";
    }
}
