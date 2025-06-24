<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;

    protected $fillable = [
        'post_id',
        'author_name',
        'author_email',
        'content',
        'approved',
    ];

    protected $casts = [
        'approved' => 'boolean',
    ];

    /**
     * 投稿とのリレーション
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * 承認済みコメントのスコープ
     */
    public function scopeApproved($query)
    {
        return $query->where('approved', true);
    }
}
