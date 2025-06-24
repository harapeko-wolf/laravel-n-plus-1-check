<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class AuthorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        echo "著者データを生成中...\n";
        
        $faker = Faker::create('ja_JP');
        $authors = [];
        
        // 100人の著者を生成
        for ($i = 1; $i <= 100; $i++) {
            $authors[] = [
                'name' => $faker->name,
                'email' => $faker->unique()->safeEmail,
                'bio' => $faker->optional(0.7)->realText(200),
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // メモリ効率のため1000件ずつ挿入
            if ($i % 1000 === 0 || $i === 100) {
                DB::table('authors')->insert($authors);
                $authors = [];
                echo "著者 {$i} 件生成完了\n";
            }
        }
        
        echo "著者データ生成完了: 100件\n";
    }
}
