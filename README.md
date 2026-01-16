# Laravel Portfolio

## プロジェクト概要
このプロジェクトは、記事（Article）とコメント（Comment）を管理する **Laravel 12 REST API アプリケーション**です。Docker を使用してコンテナ化されており、フロントエンドは含まれていません。

## 主な機能
- 記事の投稿、一覧表示、詳細表示
- 記事へのいいね機能
- 記事へのコメント投稿

## 技術スタック
- **フレームワーク**: Laravel 12
- **言語**: PHP 8.5+
- **データベース**: SQLite（開発環境）
- **コンテナ**: Docker Compose
- **認証**: Laravel Sanctum（将来の実装予定）
- **テスト**: PHPUnit

## セットアップ手順

### 必要要件
- Docker
- Docker Compose

### 初期セットアップ
以下のコマンドを実行してください：
```bash
make setup
```
これにより、以下が自動的に実行されます：
- Docker コンテナの起動
- Composer 依存関係のインストール
- アプリケーションキーの生成
- データベースマイグレーション
- 開発サーバーの起動

### 開発サーバーの起動
```bash
make serve
```
アプリケーションは `http://localhost:8000` で利用可能です。

## API エンドポイント

### 記事
- `GET /api/articles` - 記事一覧を取得
- `POST /api/articles` - 新規記事を作成
- `GET /api/articles/{id}` - 特定の記事を取得
- `POST /api/articles/{id}/likes` - 記事にいいねを追加

### コメント
- `POST /api/articles/{article}/comments` - 記事にコメントを追加

## テスト
以下のコマンドでテストを実行できます：
```bash
docker compose exec laravel-app-server php artisan test
```

## ディレクトリ構造
```
├── src/
│   ├── app/
│   │   ├── Http/Controllers/Api/  # APIコントローラー
│   │   └── Models/               # Eloquentモデル
│   ├── routes/api.php            # APIルート定義
│   ├── database/                 # マイグレーションとシーダー
│   └── tests/                    # テストコード
└── docker-config/                # Docker設定
```
