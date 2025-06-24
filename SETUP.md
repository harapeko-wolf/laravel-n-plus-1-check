# 詳細セットアップガイド

## 🔧 環境要件

- Docker Desktop
- Git
- 4GB以上のメモリ（推奨）

## 📋 セットアップ手順

### 1. プロジェクトの取得

```bash
git clone <repository-url>
cd laravel-n-plus-1-check
```

### 2. Docker環境の起動

```bash
# Docker コンテナをバックグラウンドで起動
docker-compose up -d

# 起動確認
docker-compose ps
```

**期待される出力:**
```
NAME                        COMMAND                  SERVICE   STATUS
laravel-n-plus-1-app        "docker-php-entrypoi…"   app       Up
laravel-n-plus-1-mysql      "docker-entrypoint.s…"   mysql     Up  
laravel-n-plus-1-nginx      "/docker-entrypoint.…"   nginx     Up
```

### 3. Laravelアプリケーションの初期化

```bash
# Composer依存関係インストール
docker-compose exec app composer install

# アプリケーションキー生成
docker-compose exec app php artisan key:generate

# 設定ファイル確認
docker-compose exec app cat .env | grep DB_
```

### 4. データベースの準備

```bash
# マイグレーション実行
docker-compose exec app php artisan migrate

# 成功確認
docker-compose exec app php artisan migrate:status
```

### 5. 大量サンプルデータの生成

⚠️ **注意**: データ生成には3-5分かかります

```bash
# シーダー実行
docker-compose exec app php artisan db:seed
```

**進行状況例:**
```
=== Laravel N+1 クエリ問題デモ用データ生成開始 ===
想定時間: 3-5分

著者データを生成中...
著者 100 件生成完了
著者データ生成完了: 100件

投稿データを生成中...
投稿 1000 件生成完了
投稿 2000 件生成完了
...
投稿データ生成完了: 100,000件

コメントデータを生成中...
コメント 1000 件生成完了
...
コメントデータ生成完了: 約800,000件

=== データ生成完了 ===
```

### 6. 動作確認

```bash
# ブラウザでアクセス
open http://localhost

# または curlで確認
curl -I http://localhost
```

## 🔍 トラブルシューティング

### Docker関連

#### ポート競合エラー
```bash
# 他のサービスがポート80を使用している場合
docker-compose down
# docker-compose.ymlでポートを変更（例: "8080:80"）
docker-compose up -d
```

#### コンテナが起動しない
```bash
# ログ確認
docker-compose logs app
docker-compose logs mysql
docker-compose logs nginx

# 強制再起動
docker-compose down -v
docker-compose up -d --force-recreate
```

### Database関連

#### 接続エラー
```bash
# MySQL接続確認
docker-compose exec mysql mysql -u laravel -p laravel_n_plus_1

# 設定確認
docker-compose exec app php artisan tinker
>>> DB::connection()->getPdo();
```

#### メモリ不足
```bash
# MySQLメモリ設定を調整
# docker/mysql/my.cnf を編集
innodb_buffer_pool_size=512M  # 1Gから削減

# 再起動
docker-compose restart mysql
```

### アプリケーション関連

#### 500エラー
```bash
# ログ確認
docker-compose exec app tail -f storage/logs/laravel.log

# 権限確認
docker-compose exec app chown -R www:www storage bootstrap/cache
```

#### N+1デモが重すぎる
```bash
# 少ないデータで再作成
docker-compose exec app php artisan migrate:fresh

# PostSeeder.php で件数を調整（例: 1,000件）
# 再シード
docker-compose exec app php artisan db:seed
```

## 🔧 カスタマイズ

### データ量の調整

**少量データでテスト:**
```php
// database/seeders/PostSeeder.php
for ($i = 1; $i <= 1000; $i++) { // 100,000 → 1,000に変更

// database/seeders/CommentSeeder.php  
$commentCount = $faker->numberBetween(1, 5); // 5-15 → 1-5に変更
```

**再生成:**
```bash
docker-compose exec app php artisan migrate:fresh --seed
```

### パフォーマンス監視

**Laravel Debugbarの追加:**
```bash
docker-compose exec app composer require barryvdh/laravel-debugbar --dev
docker-compose exec app php artisan vendor:publish --provider="Barryvdh\Debugbar\ServiceProvider"
```

**Laravel Telescopeの追加:**
```bash
docker-compose exec app composer require laravel/telescope --dev
docker-compose exec app php artisan telescope:install
docker-compose exec app php artisan migrate
```

### 追加の最適化手法

**新しいサービスメソッドの追加例:**
```php
// app/Services/PostDemoService.php
public function getPostsWithCache(int $limit = 50): array
{
    return Cache::remember("posts.{$limit}", 60, function () use ($limit) {
        return $this->getPostsWithEagerLoading($limit);
    });
}
```

## 📊 監視・測定

### パフォーマンス測定
```bash
# Apache Bench（複数リクエスト）
ab -n 10 -c 2 http://localhost/posts/n1
ab -n 10 -c 2 http://localhost/posts/with

# curl（レスポンス時間）
curl -w "@curl-format.txt" -o /dev/null -s http://localhost/posts/n1
```

**curl-format.txt:**
```
     time_namelookup:  %{time_namelookup}\n
        time_connect:  %{time_connect}\n
     time_appconnect:  %{time_appconnect}\n
    time_pretransfer:  %{time_pretransfer}\n
       time_redirect:  %{time_redirect}\n
  time_starttransfer:  %{time_starttransfer}\n
                     ----------\n
          time_total:  %{time_total}\n
```

### データベース監視
```bash
# 実行中のクエリ確認
docker-compose exec mysql mysql -u root -p -e "SHOW PROCESSLIST;"

# スロークエリログ
docker-compose exec mysql tail -f /var/log/mysql/slow.log
```

## 🧪 ベンチマークテスト

```bash
# N+1問題（重い）
time curl -s http://localhost/posts/n1?limit=100 > /dev/null

# Eager Loading（軽い）  
time curl -s http://localhost/posts/with?limit=100 > /dev/null

# JOIN最適化（最軽量）
time curl -s http://localhost/posts/join?limit=100 > /dev/null
```

## 🔄 リセット・再起動

### 完全リセット
```bash
# 全コンテナ・ボリューム削除
docker-compose down -v --rmi all

# 再構築
docker-compose up -d --build
docker-compose exec app composer install
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan migrate --seed
```

### クイックリセット（データのみ）
```bash
docker-compose exec app php artisan migrate:fresh --seed
``` 