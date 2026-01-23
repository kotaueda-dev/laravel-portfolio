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
- **フロントエンド**: Next.js 14 + TypeScript + Tailwind CSS
- **言語**: PHP 8.5+、TypeScript
- **データベース**: SQLite（開発環境）
- **キャッシュ**: Redis
- **コンテナ**: Docker Compose
- **Webサーバ**: Nginx
- **認証**: Laravel Sanctum（将来の実装予定）
- **テスト**: PHPUnit
- **パッケージ管理**: pnpm（ワークスペース）

## セットアップ手順

### 必要要件
- Docker & Docker Compose
- Node.js 24 LTS （nvm で管理）
- pnpm

### 初期セットアップ
```bash
# 1. Laravelセットアップ
make setup

# 2. フロントエンド依存関係インストール
pnpm install

# 3. nvm設定確認
nvm use
```

### 開発サーバーの起動

**ターミナル 1: バックエンド**
```bash
make up          # Docker コンテナ起動
make logs        # ログ確認
```

**ターミナル 2: フロントエンド**
```bash
cd frontend && pnpm dev
```

- バックエンド API: `http://localhost:8000/api`
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
