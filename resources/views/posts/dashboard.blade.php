<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laravel N+1 デモ - ダッシュボード</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                    <a class="nav-link active" href="{{ route('posts.dashboard') }}">ダッシュボード</a>
                </div>
            </div>
        </nav>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3>N+1クエリ問題 体験デモ</h3>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>🚨 N+1問題を体験する</h5>
                                <p class="text-muted">
                                    投稿リストの表示で、各投稿ごとに著者情報とコメント数を個別にクエリする非効率なパターンです。
                                    投稿数が増えるほど著しくパフォーマンスが低下します。
                                </p>
                                <a href="{{ route('posts.n1') }}" class="btn btn-danger">N+1問題デモ</a>
                            </div>
                            <div class="col-md-6">
                                <h5>✅ Eager Loading最適化</h5>
                                <p class="text-muted">
                                    withメソッドを使って関連データを事前読み込みする最適化手法です。
                                    クエリ数を大幅に削減できます。
                                </p>
                                <a href="{{ route('posts.with') }}" class="btn btn-success">Eager Loading デモ</a>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <h5>📊 withCount最適化</h5>
                                <p class="text-muted">
                                    関連モデルの件数のみが必要な場合の最適化手法です。
                                    メモリ使用量を抑えつつ高速に件数を取得できます。
                                </p>
                                <a href="{{ route('posts.with-count') }}" class="btn btn-info">withCount デモ</a>
                            </div>
                            <div class="col-md-6">
                                <h5>⚡ JOIN最適化</h5>
                                <p class="text-muted">
                                    SQLのJOINを使って単一クエリで全てのデータを取得する最高速の手法です。
                                    複雑なクエリも1回で実行できます。
                                </p>
                                <a href="{{ route('posts.join') }}" class="btn btn-warning">JOIN最適化 デモ</a>
                            </div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <h5>🔄 Chunk分割処理</h5>
                                <p class="text-muted">
                                    大量データを分割して処理するメモリ効率の良い手法です。
                                    バッチ処理や大量データ処理に最適です。
                                </p>
                                <a href="{{ route('posts.chunk') }}" class="btn btn-secondary">Chunk処理 デモ</a>
                            </div>
                            <div class="col-md-6">
                                <h5>📈 パフォーマンス比較</h5>
                                <div class="alert alert-info">
                                    <h6>期待される結果 (50件処理時)</h6>
                                    <ul class="mb-0">
                                        <li><strong>N+1:</strong> 100+ クエリ, 1-5秒</li>
                                        <li><strong>with:</strong> 3-5 クエリ, 100-300ms</li>
                                        <li><strong>JOIN:</strong> 1 クエリ, 50-150ms</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 使用手順 -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h5>🎯 使用手順</h5>
                    </div>
                    <div class="card-body">
                        <ol>
                            <li><strong>データ準備:</strong> シーダーで大量データを生成済み（Author: 100件、Post: 100,000件、Comment: 1,000,000件）</li>
                            <li><strong>N+1デモ:</strong> まず「N+1問題デモ」を実行して問題を体感</li>
                            <li><strong>最適化比較:</strong> 他の手法を順次試してパフォーマンス差を確認</li>
                            <li><strong>件数調整:</strong> 各ページで取得件数を変更して負荷を調整可能</li>
                            <li><strong>クエリ確認:</strong> 右側のクエリログで実際のSQL文を確認</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>