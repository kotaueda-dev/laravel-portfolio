# Laravel Portfolio - システム設計書

## 1. プロジェクト概要

### 1.1 目的
記事（Article）とコメント（Comment）を管理するポートフォリオアプリケーション。REST APIを提供し、記事の投稿・閲覧・いいね機能、およびコメント投稿機能を実装。

### 1.2 技術スタック
- **フレームワーク**: Laravel 12.0
- **PHP**: 8.5以上
- **データベース**: SQLite（開発環境）
- **コンテナ**: Docker Compose
- **認証**: Laravel Sanctum 4.0（実装予定）
- **テスト**: PHPUnit 11.5.3
- **コード品質**: Laravel Pint

### 1.3 主要機能
- 記事の一覧表示・詳細表示・投稿
- 記事へのいいね機能
- 記事へのコメント投稿
- RESTful APIのみ（フロントエンドなし）

---

## 2. システムアーキテクチャ

### 2.1 全体構成
```
┌─────────────┐
│   Client    │
│  (Frontend) │
└──────┬──────┘
       │ HTTP/REST API
       ↓
┌────────────────────────────┐
│   Docker Container         │
│  ┌────────────────────┐    │
│  │ Laravel App Server │    │
│  │  - PHP 8.5         │    │
│  │  - Laravel 12      │    │
│  │  - Port: 8000      │    │
│  └─────────┬──────────┘    │
│            │               │
│            ↓               │
│  ┌────────────────────┐    │
│  │  SQLite Database   │    │
│  │  database.sqlite   │    │
│  └────────────────────┘    │
└────────────────────────────┘
```

### 2.2 レイヤー構成
```
routes/api.php
    ↓
Controllers (app/Http/Controllers/Api/)
    ↓
Models (app/Models/)
    ↓
Database (SQLite)
```

### 2.3 ディレクトリ構造（重要部分）
```
src/
├── app/
│   ├── Http/Controllers/Api/
│   │   ├── ArticleController.php
│   │   ├── CommentController.php
│   │   └── GreetingController.php
│   └── Models/
│       ├── Article.php
│       ├── Comment.php
│       └── User.php
├── routes/
│   ├── api.php          # REST APIルート定義
│   └── web.php          # Webルート（最小限）
├── database/
│   ├── migrations/      # データベースマイグレーション
│   └── seeders/         # テストデータ生成
└── tests/
    ├── Feature/         # 機能テスト
    └── Unit/            # 単体テスト
```

---

## 3. データベース設計

### 3.1 ER図
```
┌─────────────────┐          ┌─────────────────┐
│     articles    │          │    comments     │
├─────────────────┤          ├─────────────────┤
│ id (PK)         │1        *│ id (PK)         │
│ title           │──────────│ article_id (FK) │
│ content         │          │ message         │
│ username        │          │ created_at      │
│ like            │          │ updated_at      │
│ created_at      │          └─────────────────┘
│ updated_at      │
└─────────────────┘

┌─────────────────┐
│      users      │  ※現在未使用（将来の認証用）
├─────────────────┤
│ id (PK)         │
│ name            │
│ email           │
│ password        │
│ created_at      │
│ updated_at      │
└─────────────────┘
```

### 3.2 テーブル定義

#### articles テーブル
| カラム名   | 型              | 制約            | 説明                     |
|-----------|-----------------|-----------------|-------------------------|
| id        | BIGINT UNSIGNED | PRIMARY KEY     | 記事ID（自動採番）        |
| title     | VARCHAR(255)    | NOT NULL        | 記事タイトル             |
| content   | TEXT            | NOT NULL        | 記事本文                 |
| username  | VARCHAR(255)    | NOT NULL        | 投稿者名                 |
| like      | UNSIGNED INT    | DEFAULT 0       | いいね数                 |
| created_at| TIMESTAMP       | NULL            | 作成日時                 |
| updated_at| TIMESTAMP       | NULL            | 更新日時                 |

#### comments テーブル
| カラム名   | 型              | 制約            | 説明                     |
|-----------|-----------------|-----------------|-------------------------|
| id        | BIGINT UNSIGNED | PRIMARY KEY     | コメントID（自動採番）    |
| article_id| BIGINT UNSIGNED | FOREIGN KEY     | 記事ID（articles.id）    |
| message   | TEXT            | NOT NULL        | コメント本文             |
| created_at| TIMESTAMP       | NULL            | 作成日時                 |
| updated_at| TIMESTAMP       | NULL            | 更新日時                 |

**リレーション**: articles.id → comments.article_id (1対多)

---

## 4. API仕様

### 4.1 エンドポイント一覧

#### 記事API

##### GET /api/articles
記事一覧を取得

**リクエスト**: なし

**レスポンス例**:
```json
[
  {
    "id": 1,
    "title": "記事タイトル",
    "content": "記事本文",
    "username": "投稿者名",
    "like": 5,
    "created_at": "2025-12-11T00:00:00.000000Z",
    "updated_at": "2025-12-11T00:00:00.000000Z"
  }
]
```

##### POST /api/articles
新規記事を作成

**リクエスト**:
```json
{
  "title": "記事タイトル",
  "content": "記事本文",
  "username": "投稿者名"
}
```

**レスポンス**: 作成された記事オブジェクト

##### GET /api/articles/{id}
特定の記事を取得（IDは数値のみ）

**パスパラメータ**: 
- `id` (integer): 記事ID

**レスポンス**: 記事オブジェクト（コメント含む）

##### POST /api/articles/{id}/likes
記事にいいねを追加（IDは数値のみ）

**パスパラメータ**: 
- `id` (integer): 記事ID

**レスポンス**: 更新された記事オブジェクト

#### コメントAPI

##### POST /api/articles/{article}/comments
記事にコメントを追加（IDは数値のみ）

**パスパラメータ**: 
- `article` (integer): 記事ID

**リクエスト**:
```json
{
  "message": "コメント本文"
}
```

**レスポンス**: 作成されたコメントオブジェクト

#### その他

##### GET /api/greeting
テスト用のグリーティングAPI

**レスポンス**: 
```json
"Hello!"
```

### 4.2 共通仕様
- **ベースURL**: `http://localhost:8000`
- **Content-Type**: `application/json`
- **認証**: 現在なし（Sanctum導入予定）
- **エラーレスポンス**: Laravel標準のエラーハンドリング

---

## 5. ビジネスロジック

### 5.1 記事投稿フロー
1. クライアントから記事データ（title, content, username）を受信
2. バリデーション実行（Controllerレベル）
3. `Article::create()` でEloquent経由でデータベースに保存
4. 作成された記事オブジェクトを返却

### 5.2 いいね機能
- **実装方式**: 単純なカウンター方式
- `like`カラムをインクリメント
- ユーザー識別なし（誰でも何度でもいいね可能）
- 将来的には、ユーザー認証と組み合わせて「1ユーザー1いいね」制限を実装予定

### 5.3 コメント投稿フロー
1. 記事IDとコメント本文を受信
2. 記事の存在確認（Eloquentリレーション経由）
3. `Comment::create()` でコメント作成
4. 作成されたコメントを返却

### 5.4 Eloquentリレーション
```php
// Article Model
public function comments(): HasMany
{
    return $this->hasMany(Comment::class);
}

// Comment Model
public function article(): BelongsTo
{
    return $this->belongsTo(Article::class);
}
```

---

## 6. 開発環境

### 6.1 Docker構成
**コンテナ**: `laravel-app-server`
- **イメージ**: カスタムPHP 8.2（`docker-config/php/Dockerfile`）
- **ポート**: 8000
- **ボリューム**: `./src` → `/var/www/html`
- **カスタム設定**: `docker-config/php/php.ini`

### 6.2 開発コマンド
```bash
# 初回セットアップ
make setup

# 開発サーバー起動
make serve

# コンテナにアクセス
make app

# テスト実行
docker compose exec laravel-app-server php artisan test

# マイグレーション
docker compose exec laravel-app-server php artisan migrate

# シーダー実行
docker compose exec laravel-app-server php artisan db:seed
```

### 6.3 テスト環境
- **フレームワーク**: PHPUnit 11.5.3
- **データベース**: SQLite（メモリ内）
- **設定**: `phpunit.xml`
- テストスイート: Feature（機能テスト）、Unit（単体テスト）

---

## 7. セキュリティ

### 7.1 現在の状態
- **認証**: なし
- **認可**: なし
- **バリデーション**: 基本的なLaravelバリデーション（Controller実装次第）
- **CSRF保護**: Web routesで有効、API routesでは無効（Laravel標準）

### 7.2 今後の実装予定
- **Laravel Sanctum**: API認証（トークンベース）
- **ユーザー登録・ログイン**: User modelの活用
- **投稿権限**: 認証済みユーザーのみ記事・コメント投稿可能
- **いいね制限**: 1ユーザー1記事1いいね

---

## 8. 今後の拡張予定

### 8.1 認証機能
- [ ] ユーザー登録・ログイン（Sanctum）
- [ ] JWT/トークン認証
- [ ] パスワードリセット機能

### 8.2 機能拡張
- [ ] 記事の編集・削除機能
- [ ] コメントの編集・削除機能
- [ ] ページネーション（記事一覧）
- [ ] 記事の検索・フィルタリング
- [ ] タグ機能
- [ ] 画像アップロード

### 8.3 インフラ
- [ ] MySQL/PostgreSQLへの移行（本番環境）
- [ ] CI/CDパイプライン構築
- [ ] デプロイ自動化

---

## 9. 補足資料

### 9.1 関連ファイル
- **ルート定義**: `src/routes/api.php`
- **モデル**: `src/app/Models/`
- **マイグレーション**: `src/database/migrations/`
- **環境設定テンプレート**: `docker-config/php/.env.laravel`

### 9.2 参考ドキュメント
- [Laravel公式ドキュメント](https://laravel.com/docs)
- [Laravel Sanctumドキュメント](https://laravel.com/docs/sanctum)
- [Eloquent ORMガイド](https://laravel.com/docs/eloquent)

---

**最終更新**: 2025年12月11日
