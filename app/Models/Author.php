<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'bio',
    ];

    /**
     * 投稿とのリレーション
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * 公開済み投稿のみ取得
     */
    public function publishedPosts()
    {
        return $this->hasMany(Post::class)->where('published', true);
    }
}
