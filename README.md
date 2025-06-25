# Laravel N+1 クエリ問題 体験デモ

このプロジェクトは、LaravelにおけるN+1クエリ問題を実際に体験し、様々な最適化手法を比較できるデモ環境です。

## 🎯 学習目標

- **N+1クエリ問題**の理解と体感
- **Eager Loading**による最適化効果の実感
- **JOIN**、**withCount**、**chunk**処理などの比較
- 実際のパフォーマンス差の可視化

## 📊 デモデータ

- **著者**: 100件
- **投稿**: 100,000件  
- **コメント**: 約500,000-1,500,000件

## 🚀 クイックスタート

### 1. 環境準備

```bash
# リポジトリクローン
git clone <repository-url>
cd laravel-n-plus-1-check

# Docker環境起動
docker-compose up -d

# Composerインストール
docker-compose exec app composer install
```

### 2. データベース初期化

```bash
# アプリケーションキー生成
docker-compose exec app php artisan key:generate

# マイグレーション実行
docker-compose exec app php artisan migrate

# 大量サンプルデータ生成（3-5分）
docker-compose exec app php artisan db:seed
```

### 3. アクセス

ブラウザで http://localhost にアクセス

## 📚 デモページ一覧

| URL | 説明 | 期待されるパフォーマンス |
|-----|------|----------------------|
| `/posts-n1` | N+1問題発生パターン | 100+ クエリ, 1-5秒 |
| `/posts-with` | Eager Loading最適化 | 3-5 クエリ, 100-300ms |
| `/posts-with-count` | withCount最適化 | 2-3 クエリ, 50-200ms |
| `/posts-join` | JOIN最適化 | 1 クエリ, 50-150ms |
| `/posts-chunk` | chunk分割処理 | 複数回実行, メモリ効率◎ |
| `/posts/dashboard` | 概要・ナビゲーション | Laravel Debugbar表示 |

## 🏗️ アーキテクチャ

```
app/
├── Http/Controllers/
│   └── PostDemoController.php     # デモページのコントローラー
├── Models/
│   ├── Author.php                 # 著者モデル
│   ├── Post.php                   # 投稿モデル  
│   └── Comment.php                # コメントモデル
└── Services/
    └── PostDemoService.php        # N+1問題とその解決策の実装

database/
├── migrations/                    # データベース設計
└── seeders/                       # 大量データ生成
    ├── AuthorSeeder.php
    ├── PostSeeder.php
    └── CommentSeeder.php

resources/views/posts/
├── demo.blade.php                 # デモ結果表示
└── dashboard.blade.php            # ダッシュボード

docker/                            # Docker設定
├── php/
├── nginx/
└── mysql/
```

## 🔧 技術スタック

- **PHP**: 8.4.8
- **Laravel**: 11.45.1
- **MySQL**: 8.4.5
- **Docker**: Nginx + PHP-FPM + MySQL構成
- **Bootstrap**: 5.1.3（UI）
- **Laravel Debugbar**: 3.15.4（デバッグ・パフォーマンス解析）

## 📈 学習ポイント

### N+1問題とは？
```php
// ❌ N+1問題：投稿数分だけクエリが発生
$posts = Post::all();
foreach ($posts as $post) {
    echo $post->author->name;     // 毎回クエリ実行
    echo $post->comments->count(); // 毎回クエリ実行
}
```

### 解決策1: Eager Loading
```php
// ✅ 事前読み込みで3クエリに削減
$posts = Post::with(['author', 'comments'])->get();
foreach ($posts as $post) {
    echo $post->author->name;     // メモリから取得
    echo $post->comments->count(); // メモリから取得
}
```

### 解決策2: withCount
```php
// ✅ 件数のみ必要な場合はさらに最適化
$posts = Post::with('author')->withCount('comments')->get();
foreach ($posts as $post) {
    echo $post->author->name;
    echo $post->comments_count;   // 集計済み件数
}
```

### 解決策3: JOIN最適化
```php
// ✅ 単一クエリで全て取得（最高速）
$posts = DB::select("
    SELECT p.*, a.name as author_name, 
           COUNT(c.id) as comments_count
    FROM posts p
    JOIN authors a ON p.author_id = a.id
    LEFT JOIN comments c ON p.id = c.post_id
    GROUP BY p.id
");
```

## 🎮 使い方

1. **N+1問題を体感**: まず `/posts/n1` にアクセスして重いページを体験
2. **最適化効果を確認**: `/posts/with` で劇的な改善を実感  
3. **手法を比較**: 他のページで様々な最適化手法を比較
4. **クエリを確認**: 右側のクエリログで実行されたSQLを確認
5. **負荷調整**: 取得件数を変更して負荷を調整
6. **Debugbar活用**: 画面下部のLaravel Debugbarでクエリ数、実行時間、メモリ使用量を詳細確認

## 🔍 トラブルシューティング

### データが表示されない
```bash
# シーダー再実行
docker-compose exec app php artisan migrate:fresh --seed
```

### Laravel Debugbarが表示されない
```bash
# Debugbarパッケージ確認（既にインストール済み）
docker-compose exec app composer show barryvdh/laravel-debugbar

# キャッシュクリア
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan view:clear
```

### Dockerコンテナが起動しない
```bash
# コンテナ状況確認
docker-compose ps

# ログ確認
docker-compose logs app
docker-compose logs mysql
```

### メモリ不足エラー
```bash
# MySQL設定を調整（docker/mysql/my.cnf）
innodb_buffer_pool_size=512M  # デフォルト1Gから削減
```

## 📝 カスタマイズ

### データ量の調整
`database/seeders/` 内のシーダーファイルで件数を調整可能

### 新しい最適化手法の追加
1. `PostDemoService` にメソッド追加
2. `PostDemoController` にアクション追加  
3. ルート定義とビュー作成

## 🤝 コントリビューション

プルリクエストやIssueをお待ちしております！

## 📈 アップグレード履歴

### v2.0 (2025-06-25)
- **Laravel 8.83.29 → 11.45.1** へのメジャーアップグレード完了
- **PHP 8.4.8** 対応
- **Laravel Debugbar 3.15.4** 導入
- CORS設定をLaravel 11標準に移行
- 100万件以上のテストデータで動作確認済み

### v1.0 
- Laravel 8によるN+1問題デモ環境構築

## 📄 ライセンス

MIT License
