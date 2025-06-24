<?php

namespace App\Services;

use App\Models\Post;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class PostDemoService
{
    /**
     * N+1クエリ問題を発生させるパターン（ページネーション対応）
     */
    public function getPostsWithN1QueryPaginated(int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        $query = Post::published()->orderBy('created_at', 'desc');
        $total = $query->count();
        
        $posts = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $result = [];
        foreach ($posts as $post) {
            // N+1問題: 各投稿ごとにauthorとcommentsを個別にクエリする
            $result[] = [
                'id' => $post->id,
                'title' => $post->title,
                'body' => substr($post->body, 0, 200),
                'author_name' => $post->author->name, // N+1発生
                'author_email' => $post->author->email, // 同上
                'comments_count' => $post->comments()->count(), // N+1発生
                'created_at' => $post->created_at->format('Y-m-d H:i:s'),
            ];
        }

        return new LengthAwarePaginator(
            $result,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'pageName' => 'page']
        );
    }

    /**
     * Eager Loading (with) による最適化（ページネーション対応）
     */
    public function getPostsWithEagerLoadingPaginated(int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        $query = Post::with(['author', 'comments'])
            ->published()
            ->orderBy('created_at', 'desc');
            
        $total = Post::published()->count();
        
        $posts = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $result = [];
        foreach ($posts as $post) {
            $result[] = [
                'id' => $post->id,
                'title' => $post->title,
                'body' => substr($post->body, 0, 200),
                'author_name' => $post->author->name,
                'author_email' => $post->author->email,
                'comments_count' => $post->comments->count(), // メモリ上でカウント
                'created_at' => $post->created_at->format('Y-m-d H:i:s'),
            ];
        }

        return new LengthAwarePaginator(
            $result,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'pageName' => 'page']
        );
    }

    /**
     * withCount を使った最適化（ページネーション対応）
     */
    public function getPostsWithCountPaginated(int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        $query = Post::with('author')
            ->withCount('comments')
            ->published()
            ->orderBy('created_at', 'desc');
            
        $total = Post::published()->count();
        
        $posts = $query->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        $result = [];
        foreach ($posts as $post) {
            $result[] = [
                'id' => $post->id,
                'title' => $post->title,
                'body' => substr($post->body, 0, 200),
                'author_name' => $post->author->name,
                'author_email' => $post->author->email,
                'comments_count' => $post->comments_count, // withCountで取得
                'created_at' => $post->created_at->format('Y-m-d H:i:s'),
            ];
        }

        return new LengthAwarePaginator(
            $result,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'pageName' => 'page']
        );
    }

    /**
     * JOIN クエリビルダーによる最適化（ページネーション対応）
     */
    public function getPostsWithJoinPaginated(int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        // サブクエリでコメント数を取得
        $commentsCountSubquery = DB::table('comments')
            ->select('post_id', DB::raw('COUNT(*) as count'))
            ->where('approved', true)
            ->groupBy('post_id');
        
        // メインクエリ
        $query = DB::table('posts')
            ->select([
                'posts.id',
                'posts.title',
                'posts.body',
                'posts.created_at',
                'authors.name as author_name',
                'authors.email as author_email',
                DB::raw('COALESCE(comment_counts.count, 0) as comments_count')
            ])
            ->join('authors', 'posts.author_id', '=', 'authors.id')
            ->leftJoinSub($commentsCountSubquery, 'comment_counts', function ($join) {
                $join->on('posts.id', '=', 'comment_counts.post_id');
            })
            ->where('posts.published', true)
            ->orderBy('posts.created_at', 'desc');

        // 総件数を取得
        $total = DB::table('posts')
            ->where('published', true)
            ->count();
        
        // ページング適用
        $results = $query
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        // 結果を整形
        $posts = $results->map(function ($row) {
            return [
                'id' => $row->id,
                'title' => $row->title,
                'body' => substr($row->body, 0, 200),
                'author_name' => $row->author_name,
                'author_email' => $row->author_email,
                'comments_count' => (int) $row->comments_count,
                'created_at' => $row->created_at,
            ];
        })->toArray();

        return new LengthAwarePaginator(
            $posts,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'pageName' => 'page']
        );
    }

    /**
     * chunk による分割処理（ページネーション対応）
     */
    public function getPostsWithChunkPaginated(int $perPage = 20, int $page = 1): LengthAwarePaginator
    {
        $total = Post::published()->count();
        $result = [];
        $targetStart = ($page - 1) * $perPage;
        $targetEnd = $targetStart + $perPage;
        $currentIndex = 0;
        
        Post::with(['author', 'comments'])
            ->published()
            ->orderBy('created_at', 'desc')
            ->chunk(1000, function ($posts) use (&$result, &$currentIndex, $targetStart, $targetEnd) {
                foreach ($posts as $post) {
                    if ($currentIndex >= $targetStart && $currentIndex < $targetEnd) {
                        $result[] = [
                            'id' => $post->id,
                            'title' => $post->title,
                            'body' => substr($post->body, 0, 200),
                            'author_name' => $post->author->name,
                            'author_email' => $post->author->email,
                            'comments_count' => $post->comments->count(),
                            'created_at' => $post->created_at->format('Y-m-d H:i:s'),
                        ];
                    }
                    $currentIndex++;
                    
                    // 必要な分だけ取得したら終了
                    if ($currentIndex >= $targetEnd) {
                        return false;
                    }
                }
            });

        return new LengthAwarePaginator(
            $result,
            $total,
            $perPage,
            $page,
            ['path' => request()->url(), 'pageName' => 'page']
        );
    }

    /**
     * クエリ統計情報を取得
     */
    public function getQueryStats(): array
    {
        return [
            'total_posts' => Post::count(),
            'published_posts' => Post::published()->count(),
            'total_authors' => \App\Models\Author::count(),
            'total_comments' => \App\Models\Comment::count(),
        ];
    }
} 