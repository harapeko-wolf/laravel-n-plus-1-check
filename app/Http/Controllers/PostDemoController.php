<?php

namespace App\Http\Controllers;

use App\Services\PostDemoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostDemoController extends Controller
{
    private PostDemoService $postDemoService;

    public function __construct(PostDemoService $postDemoService)
    {
        $this->postDemoService = $postDemoService;
    }

    /**
     * N+1クエリ問題のデモページ
     */
    public function indexN1(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);
        $page = (int) $request->get('page', 1);
        
        // ページサイズは10, 20, 50, 100のみ許可
        if (!in_array($perPage, [10, 20, 50, 100])) {
            $perPage = 20;
        }
        
        // クエリログを有効化
        DB::enableQueryLog();
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        $paginatedPosts = $this->postDemoService->getPostsWithN1QueryPaginated($perPage, $page);
        $posts = $paginatedPosts->items();

        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        $queryLog = DB::getQueryLog();

        $stats = [
            'method' => 'N+1クエリ問題',
            'execution_time' => round(($endTime - $startTime) * 1000, 2) . 'ms',
            'query_count' => count($queryLog),
            'memory_used' => round(($endMemory - $startMemory) / 1024 / 1024, 2) . 'MB',
            'posts_count' => count($posts),
        ];

        return view('posts.demo', compact('posts', 'stats', 'queryLog', 'paginatedPosts'));
    }

    /**
     * Eager Loading による最適化デモ
     */
    public function indexWith(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);
        $page = (int) $request->get('page', 1);
        
        // ページサイズは10, 20, 50, 100のみ許可
        if (!in_array($perPage, [10, 20, 50, 100])) {
            $perPage = 20;
        }
        
        DB::enableQueryLog();
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        $paginatedPosts = $this->postDemoService->getPostsWithEagerLoadingPaginated($perPage, $page);
        $posts = $paginatedPosts->items();

        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        $queryLog = DB::getQueryLog();

        $stats = [
            'method' => 'Eager Loading (with)',
            'execution_time' => round(($endTime - $startTime) * 1000, 2) . 'ms',
            'query_count' => count($queryLog),
            'memory_used' => round(($endMemory - $startMemory) / 1024 / 1024, 2) . 'MB',
            'posts_count' => count($posts),
        ];

        return view('posts.demo', compact('posts', 'stats', 'queryLog', 'paginatedPosts'));
    }

    /**
     * withCount による最適化デモ
     */
    public function indexWithCount(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);
        $page = (int) $request->get('page', 1);
        
        // ページサイズは10, 20, 50, 100のみ許可
        if (!in_array($perPage, [10, 20, 50, 100])) {
            $perPage = 20;
        }
        
        DB::enableQueryLog();
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        $paginatedPosts = $this->postDemoService->getPostsWithCountPaginated($perPage, $page);
        $posts = $paginatedPosts->items();

        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        $queryLog = DB::getQueryLog();

        $stats = [
            'method' => 'withCount 最適化',
            'execution_time' => round(($endTime - $startTime) * 1000, 2) . 'ms',
            'query_count' => count($queryLog),
            'memory_used' => round(($endMemory - $startMemory) / 1024 / 1024, 2) . 'MB',
            'posts_count' => count($posts),
        ];

        return view('posts.demo', compact('posts', 'stats', 'queryLog', 'paginatedPosts'));
    }

    /**
     * JOIN による最適化デモ
     */
    public function indexJoin(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);
        $page = (int) $request->get('page', 1);
        
        // ページサイズは10, 20, 50, 100のみ許可
        if (!in_array($perPage, [10, 20, 50, 100])) {
            $perPage = 20;
        }
        
        DB::enableQueryLog();
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        $paginatedPosts = $this->postDemoService->getPostsWithJoinPaginated($perPage, $page);
        $posts = $paginatedPosts->items();

        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        $queryLog = DB::getQueryLog();

        $stats = [
            'method' => 'JOIN クエリ最適化',
            'execution_time' => round(($endTime - $startTime) * 1000, 2) . 'ms',
            'query_count' => count($queryLog),
            'memory_used' => round(($endMemory - $startMemory) / 1024 / 1024, 2) . 'MB',
            'posts_count' => count($posts),
        ];

        return view('posts.demo', compact('posts', 'stats', 'queryLog', 'paginatedPosts'));
    }

    /**
     * chunk による分割処理デモ
     */
    public function indexChunk(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);
        $page = (int) $request->get('page', 1);
        
        // ページサイズは10, 20, 50, 100のみ許可
        if (!in_array($perPage, [10, 20, 50, 100])) {
            $perPage = 20;
        }
        
        DB::enableQueryLog();
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        $paginatedPosts = $this->postDemoService->getPostsWithChunkPaginated($perPage, $page);
        $posts = $paginatedPosts->items();

        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        $queryLog = DB::getQueryLog();

        $stats = [
            'method' => 'Chunk 分割処理',
            'execution_time' => round(($endTime - $startTime) * 1000, 2) . 'ms',
            'query_count' => count($queryLog),
            'memory_used' => round(($endMemory - $startMemory) / 1024 / 1024, 2) . 'MB',
            'posts_count' => count($posts),
        ];

        return view('posts.demo', compact('posts', 'stats', 'queryLog', 'paginatedPosts'));
    }

    /**
     * ダッシュボード（メトリクス確認）
     */
    public function dashboard()
    {
        return view('posts.dashboard');
    }
}
