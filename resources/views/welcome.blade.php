<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Laravel N+1 クエリ問題デモ</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style>
            body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
            .hero-section { color: white; text-align: center; padding: 100px 0; }
            .demo-card { border: none; border-radius: 15px; box-shadow: 0 8px 30px rgba(0,0,0,0.12); }
        </style>
    </head>
    <body>
        <div class="hero-section">
            <div class="container">
                <h1 class="display-4 fw-bold mb-4">Laravel N+1 クエリ問題デモ</h1>
                <p class="lead mb-5">パフォーマンス問題を体感し、最適化手法を学ぼう</p>
                
                <div class="row justify-content-center">
                    <div class="col-md-10">
                        <div class="card demo-card">
                            <div class="card-body p-5">
                                <div class="row text-center">
                                    <div class="col-md-4 mb-4">
                                        <div class="text-danger mb-3">
                                            <i class="fas fa-exclamation-triangle fa-3x"></i>
                                        </div>
                                        <h5>🚨 N+1問題</h5>
                                        <p class="text-muted">非効率なクエリパターンを体験</p>
                                        <a href="{{ route('posts.n1') }}" class="btn btn-danger">試してみる</a>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <div class="text-success mb-3">
                                            <i class="fas fa-rocket fa-3x"></i>
                                        </div>
                                        <h5>✅ 最適化</h5>
                                        <p class="text-muted">Eager Loadingで高速化</p>
                                        <a href="{{ route('posts.with') }}" class="btn btn-success">試してみる</a>
                                    </div>
                                    <div class="col-md-4 mb-4">
                                        <div class="text-info mb-3">
                                            <i class="fas fa-chart-line fa-3x"></i>
                                        </div>
                                        <h5>📊 比較</h5>
                                        <p class="text-muted">パフォーマンス差を確認</p>
                                        <a href="{{ route('posts.dashboard') }}" class="btn btn-info">ダッシュボード</a>
                                    </div>
                                </div>
                                
                                <hr class="my-4">
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6 class="text-primary">🎯 学習目標</h6>
                                        <ul class="text-start">
                                            <li>N+1クエリ問題の理解</li>
                                            <li>Eager Loadingの効果</li>
                                            <li>最適化手法の比較</li>
                                            <li>パフォーマンス測定</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6 class="text-primary">📈 テストデータ</h6>
                                        <ul class="text-start">
                                            <li>著者: 100件</li>
                                            <li>投稿: 100,000件</li>
                                            <li>コメント: 約100万件</li>
                                            <li>リアルな負荷でテスト</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
        <script src="https://kit.fontawesome.com/a076d05399.js"></script>
    </body>
</html>
