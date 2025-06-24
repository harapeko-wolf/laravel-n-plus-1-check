<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'author_id',
        'title',
        'body',
        'slug',
        'published',
    ];

    protected $casts = [
        'published' => 'boolean',
    ];

    /**
     * 著者とのリレーション
     */
    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    /**
     * コメントとのリレーション
     */
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    /**
     * 承認済みコメントのみ取得
     */
    public function approvedComments()
    {
        return $this->hasMany(Comment::class)->where('approved', true);
    }

    /**
     * 公開済み投稿のスコープ
     */
    public function scopePublished($query)
    {
        return $query->where('published', true);
    }
}
