# Laravel N+1 体験サンプル環境設計

## 1. 目的
- **N+1 クエリ問題** を体感し、解消策によるパフォーマンス差を可視化  
- Docker を立ち上げてすぐに「重いページ」と「最適化済みページ」をリンクで切り替えて比較できる  

---

## 2. 想定ディレクトリ構成

app/
├─ Http/
│ └─ Controllers/ ← ページ別コントローラ
├─ Models/ ← リレーション定義のみ
├─ Services/ ← クエリ最適化を含むビジネスロジック
└─ ViewModels/ ← Blade に渡すデータ整形
routes/
└─ web.php ← デモ用ルート定義
docker/
└─ ... ← PHP / MySQL 等の設定
database/
└─ seeders/ ← 大量データ生成シーダ
resources/
└─ views/ ← Blade テンプレート

### 役割の切り分け
| レイヤ         | 主な責務 | 備考 |
| -------------- | -------- | ---- |
| **Controller** | ルート単位で Service を呼び出し、ViewModel を生成して View へ渡す | 例：`PostDemoController@indexWith()` |
| **Service**    | N+1 パターン切替・クエリ実装 (`with` / `join` など) | Fat Controller を避ける |
| **Model**      | テーブル / リレーション定義のみ | ビジネスロジックを持たせない |
| **ViewModel**  | Blade 用の整形・集計（必要に応じて） | 任意 |
| **Seeder**     | Faker で 100 万件超をバルク投入 | 起動直後でも体感差がわかる |

> **ポイント**  
> - Controller は最小限ルーティングの受け口として配置し、重い処理は Service へ委譲  
> - ルート例：  
>   - `/posts-n1` → `PostDemoController@indexN1()`  
>   - `/posts-with` → `PostDemoController@indexWith()`  
>   - `/posts-join` → `PostDemoController@indexJoin()`  


---

## 3. データモデル（確定）
| モデル | 主なカラム例 | リレーション |
| ------ | ------------ | ------------- |
| `Author`  | `id`, `name` | hasMany → `Post` |
| `Post`    | `id`, `author_id`, `title`, `body` | belongsTo → `Author`<br>hasMany → `Comment` |
| `Comment` | `id`, `post_id`, `content` | belongsTo → `Post` |

---

## 4. サンプルデータ量
| テーブル | レコード数 | 備考 |
| -------- | ---------- | ---- |
| `authors`  | 100 | 著者数は控えめ |
| `posts`    | 100,000 | 1 著者あたり 1,000 投稿 |
| `comments` | 1,000,000 | 1 投稿あたり 10 コメント |

> この規模で **N+1 だと秒単位の待ち時間**、最適化後は数百 ms 程度を目安  

---

## 5. デモページ一覧
| ルート | 説明 |
| ------ | ---- |
| `/posts-n1` | **N+1 全開**（ループ内でリレーション取得） |
| `/posts-with` | `with` による **Eager Loading** |
| `/posts-join` | **JOIN クエリ** で一括取得 |
| `/posts-chunk` | `chunk` による分割処理 |
| `/dashboard` | Telescope / Debugbar でメトリクス確認 |

---

## 6. ベンチマーク方法（推奨）
1. **Laravel Debugbar**：クエリ数と合計実行時間を確認  
2. **Laravel Telescope**：リクエスト単位の詳細を可視化  
3. **wrk**：CLI で同時接続シミュレーション（高負荷テスト）  
4. ブラウザ DevTools の Network タブで TTFB を記録  

---

## 7. 参考パターン比較イメージ
| パターン | クエリ数 | 平均レスポンス | 長所 | 短所 |
| -------- | -------- | --------------- | ---- | ---- |
| N+1 | 100k+ | 5–10 s | 実装が簡単 | 極端に遅い |
| `with` | 40 | ~300 ms | 読みやすく最適 | 過剰取得リスク |
| `join` | 1 | ~150 ms | 最速 | メモリ増 |
| `chunk` | 40 | ~400 ms | メモリ節約 | コード冗長 |

---

## 8. Docker イメージ構成
| サービス | バージョン | 主な設定 |
| -------- | ---------- | -------- |
| **PHP-FPM** | 8.4 | OPcache 有効・Xdebug 無効 |
| **MySQL** | 8.4 | `innodb_buffer_pool_size=1G`（目安） |

> **不要**: Redis・MailHog は含めません（要件より削除）  
> **メモリ割り当て**: Docker 全体で **4 GB** 程度がバランス良い（ローカル環境で調整可）

---

## 9. デプロイ & 起動フロー
1. `docker compose up -d`  
2. `composer install` → `.env` 設定  
3. `php artisan migrate --seed`  
4. `http://localhost` にアクセスしメニューから各ページを確認  

---

## 10. シーダ & フェイクデータ生成
- **Faker PHP**（Laravel 標準）を使用  
- バルクインサート (`insert()` 配列) で **2–5 分** 以内に 1,100,100 件を投入  

---

## 11. 今後の拡張アイデア
- **ポリモーフィックリレーション** を絡めた N+1 デモ  
- **キャッシュ**（Redis 使用版）との速度差比較  
- **外部検索エンジン**（Elasticsearch 等）移行テスト  
- **Doctrine ORM** や **Raw SQL** でのパフォーマンス比較  

---

## 12. 確定した仕様まとめ
| 項目 | 決定内容 |
| ---- | -------- |
| データモデル | Author ↔ Post ↔ Comment（3 層） |
| フェイクデータ生成 | Faker PHP |
| Docker メモリ | 4 GB 推奨（調整可） |
| ベンチマークツール | Debugbar / Telescope / wrk |
| 不要コンテナ | Redis, MailHog |

---
