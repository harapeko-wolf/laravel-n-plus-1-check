<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel N+1 クエリ問題デモ - {{ $stats['method'] }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .performance-card {
            border-left: 4px solid #007bff;
        }
        .query-log {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin: 10px 0;
        }
        .query-sql {
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            color: #495057;
        }
        .alert-danger { border-color: #dc3545; background-color: #f8d7da; }
        .alert-warning { border-color: #ffc107; background-color: #fff3cd; }
        .alert-success { border-color: #28a745; background-color: #d4edda; }
    </style>
</head>
<body>
    <div class="container my-4">
        <!-- ナビゲーション -->
        <nav class="navbar navbar-expand-lg navbar-light bg-light mb-4">
            <div class="container-fluid">
                <a class="navbar-brand" href="/">Laravel N+1 デモ</a>
                <div class="navbar-nav">
                    <a class="nav-link" href="{{ route('posts.n1') }}">N+1問題</a>
                    <a class="nav-link" href="{{ route('posts.with') }}">Eager Loading</a>
                    <a class="nav-link" href="{{ route('posts.with-count') }}">withCount</a>
                    <a class="nav-link" href="{{ route('posts.join') }}">JOIN最適化</a>
                    <a class="nav-link" href="{{ route('posts.chunk') }}">Chunk処理</a>
                    <a class="nav-link" href="{{ route('posts.dashboard') }}">ダッシュボード</a>
                </div>
            </div>
        </nav>

        <!-- パフォーマンス統計 -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card performance-card">
                    <div class="card-header">
                        <h3 class="mb-0">{{ $stats['method'] }} - パフォーマンス結果</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-primary">{{ $stats['execution_time'] }}</h4>
                                    <small class="text-muted">実行時間</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-{{ $stats['query_count'] > 10 ? 'danger' : ($stats['query_count'] > 3 ? 'warning' : 'success') }}">
                                        {{ $stats['query_count'] }}
                                    </h4>
                                    <small class="text-muted">クエリ数</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-info">{{ $stats['memory_used'] }}</h4>
                                    <small class="text-muted">メモリ使用量</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center">
                                    <h4 class="text-secondary">{{ $stats['posts_count'] }}</h4>
                                    <small class="text-muted">投稿数</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 検証ツール -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>検証オプション</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-auto">
                                <label for="per_page" class="form-label">ページサイズ</label>
                                <select name="per_page" id="per_page" class="form-select">
                                    <option value="10" {{ request('per_page', 20) == 10 ? 'selected' : '' }}>10件/ページ</option>
                                    <option value="20" {{ request('per_page', 20) == 20 ? 'selected' : '' }}>20件/ページ</option>
                                    <option value="50" {{ request('per_page', 20) == 50 ? 'selected' : '' }}>50件/ページ</option>
                                    <option value="100" {{ request('per_page', 20) == 100 ? 'selected' : '' }}>100件/ページ</option>
                                </select>
                            </div>
                            
                            <div class="col-auto">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary d-block">実行</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- 投稿リスト -->
        <div class="row">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">投稿一覧 ({{ count($posts) }}件)</h5>
                        <small class="text-muted">
                            {{ $paginatedPosts->firstItem() }} - {{ $paginatedPosts->lastItem() }} 件 (全 {{ $paginatedPosts->total() }} 件中)
                        </small>
                    </div>
                    <div class="card-body">
                        @foreach($posts as $post)
                            <div class="border-bottom pb-3 mb-3">
                                <h6 class="text-primary">{{ $post['title'] }}</h6>
                                <p class="text-muted small mb-1">
                                    著者: {{ $post['author_name'] }} ({{ $post['author_email'] }}) | 
                                    コメント: {{ $post['comments_count'] }}件 | 
                                    {{ $post['created_at'] }}
                                </p>
                                <p class="mb-0">{{ $post['body'] }}...</p>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- ページネーション -->
                    <div class="card-footer">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">
                                    ページ {{ $paginatedPosts->currentPage() }} / {{ $paginatedPosts->lastPage() }}
                                </small>
                            </div>
                            <nav aria-label="投稿ページネーション">
                                <ul class="pagination pagination-sm mb-0">
                                    @if ($paginatedPosts->onFirstPage())
                                        <li class="page-item disabled"><span class="page-link">前へ</span></li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $paginatedPosts->appends(request()->query())->url($paginatedPosts->currentPage() - 1) }}">前へ</a>
                                        </li>
                                    @endif
                                    
                                    @for ($page = max(1, $paginatedPosts->currentPage() - 2); 
                                         $page <= min($paginatedPosts->lastPage(), $paginatedPosts->currentPage() + 2); 
                                         $page++)
                                        @if ($page == $paginatedPosts->currentPage())
                                            <li class="page-item active"><span class="page-link">{{ $page }}</span></li>
                                        @else
                                            <li class="page-item">
                                                <a class="page-link" href="{{ $paginatedPosts->appends(request()->query())->url($page) }}">{{ $page }}</a>
                                            </li>
                                        @endif
                                    @endfor
                                    
                                    @if ($paginatedPosts->hasMorePages())
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $paginatedPosts->appends(request()->query())->url($paginatedPosts->currentPage() + 1) }}">次へ</a>
                                        </li>
                                    @else
                                        <li class="page-item disabled"><span class="page-link">次へ</span></li>
                                    @endif
                                </ul>
                            </nav>
                        </div>
                    </div>
                </div>
            </div>

            <!-- クエリログ -->
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h5>実行されたクエリ ({{ count($queryLog) }}件)</h5>
                    </div>
                    <div class="card-body" style="max-height: 600px; overflow-y: auto;">
                        @foreach($queryLog as $index => $query)
                            <div class="query-log">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="badge bg-secondary">{{ $index + 1 }}</span>
                                    <span class="text-muted small">{{ round($query['time'], 2) }}ms</span>
                                </div>
                                <div class="query-sql">
                                    {{ $query['query'] }}
                                </div>
                                @if(!empty($query['bindings']))
                                    <div class="text-muted small mt-1">
                                        <strong>Bindings:</strong> {{ json_encode($query['bindings']) }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 