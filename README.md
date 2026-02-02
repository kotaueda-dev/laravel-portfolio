# Laravel Portfolio

## プロジェクト概要
このプロジェクトは、記事（Article）とコメント（Comment）を管理する **Laravel 12 REST API + Next.js フロントエンド** モノレポプロジェクトです。Docker を使用してコンテナ化されており、バックエンドは PHP-FPM で複数ワーカー処理に対応しています。

## 主な機能
- 記事の投稿、一覧表示、詳細表示
- 記事へのいいね機能
- 記事へのコメント投稿
- Next.js でのフロントエンド実装

## 技術スタック
- **バックエンド**: Laravel 12 + PHP 8.5 (PHP-FPM)
- **フロントエンド**: Next.js 16 + TypeScript + shadcn/ui
- **言語**: PHP 8.5+、TypeScript
- **データベース**: MySQL
- **キャッシュ**: Redis
- **コンテナ**: Docker Compose
- **Webサーバ**: Nginx
- **認証**: Laravel Sanctum
- **テスト**: Pest

## セットアップ手順

### 必要要件
- Docker & Docker Compose
- Node.js 24 LTS （nvm で管理）

### 初期セットアップ
```bash
# 1. Laravelセットアップ
make setup

# 2. nvm設定確認
nvm use
```

### 開発サーバーの起動

```bash
cd frontend && npm run dev
```

- フロントエンド: `http://localhost:3000`

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
make test
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
