# Laravel Portfolio - AI コーディングエージェント向け指示書

## プロジェクト概要
これは記事（Article）とコメント（Comment）を管理する **Laravel 12 REST API アプリケーション** です。Docker でコンテナ化され、Laravel の最新のアプリケーション構造に従っています。

**注記**: これは **API専用** プロジェクトです。フロントエンド実装は含まれていません。

## アーキテクチャ & 主要コンポーネント

### コアモデル & リレーション
- **Article** (`app/Models/Article.php`): 複数の Comment を保持。title、content、username、like count を格納
- **Comment** (`app/Models/Comment.php`): Article に属する。message と article 参照を格納
- **User** (`app/Models/User.php`): デフォルトの Laravel 認証モデル（現在未使用）

**データフロー**: Articles → Comments（Eloquent 経由の一対多リレーション）

### API ルート（`routes/api.php`）
- `GET /api/articles` - 全記事一覧取得
- `POST /api/articles` - 新規記事作成
- `GET /api/articles/{id}` - 記事詳細取得
- `POST /api/articles/{id}/likes` - いいね数をインクリメント
- `POST /api/articles/{article}/comments` - 記事にコメント追加

## 開発ワークフロー

### 初期セットアップ
```bash
make setup  # 1コマンドでプロジェクト初期化
# 実行内容: docker compose up、composer install、key生成、migration、artisan serve
```

### アプリケーション実行
```bash
make serve     # Laravel 開発サーバー起動（port 8000）
make app       # アプリケーションコンテナにアクセス
docker compose exec laravel-app-server php artisan <command>
```

### Docker コマンド
- `make up` - コンテナ起動
- `make down` - コンテナ停止・削除
- `make restart` - コンテナ再起動
- `make destroy` - 完全削除（volume・image含む）
- `make build` - Docker image ビルド

### テスト実行
```bash
# PHPUnit テスト実行（Unit + Feature スイート）
docker compose exec laravel-app-server php artisan test
```

### データベース
- **デフォルト**: SQLite（開発環境用）
- **ロケーション**: `src/database/database.sqlite`
- **マイグレーション**: `src/database/migrations/` - `php artisan migrate` で自動実行
- **シーダー**: `src/database/seeders/` - ArticleSeeder と CommentSeeder で テストデータ生成可能

### コード品質
- **リント**: Laravel Pint（`./vendor/bin/pint`）
- **テストフレームワーク**: PHPUnit 11.5.3 with Mockery

## プロジェクト特有のパターン & 規約

### コンテナセットアップ
- **メインコンテナ**: `laravel-app-server`（PHP 8.2+ with Laravel 環境）
- **作業ディレクトリ**: `/var/www/html`（`./src` からマウント）
- **カスタム PHP 設定**: `docker-config/php/php.ini`（コンテナビルド時に適用）

### Eloquent モデル規約
- 常に `HasFactory` トレイトを使用
- モデルプロパティを `$fillable` 配列で保護（Article、Comment で実装済み）
- リレーション定義は型ヒント付きメソッドで`HasMany|BelongsTo` を返す

### 環境 & 設定
- 環境変数: `.env.laravel` テンプレートからコピー（`docker-config/php/.env.laravel` を参照）
- 設定ファイル: `src/config/` に集約（Sanctum API 認証、Session、Cache ドライバ設定済み）
- ブートストラップ順序: `bootstrap/app.php` → `bootstrap/providers.php` → Service providers

### ファイルパーミッション
- storage/cache ディレクトリは 777 パーミッションと laravel:laravel オーナーシップが必須
- `make setup` で自動処理。手動修正が必要な場合: `docker compose exec laravel-app-server chmod -R 777 storage bootstrap/cache`

## 統合ポイント

### 外部依存パッケージ
- **Laravel Framework 12.0** - コアフレームワーク（REST ルーティング、ORM、マイグレーション）
- **Laravel Sanctum 4.0** - API トークン認証（将来の利用を想定して設定済み）
- **Laravel Tinker 2.10.1** - インタラクティブシェル（クイックテスト用）
- **FakerPHP 1.23** - テストデータ生成（Seeder で使用）

### API 専用アーキテクチャ
- **Blade テンプレートなし** - 純粋な REST API アプリケーション
- **フロントエンドアセットなし** - Vite、npm、CSS、JavaScript フロントエンドコードなし
- **Web ルートなし** - API ルートのみ設定

## よくある開発タスク

### API エンドポイント追加
1. Controller 作成: `docker compose exec laravel-app-server php artisan make:controller Api/YourController`
2. `routes/api.php` にルート追加
3. Article、Comment のパターンを参考に Eloquent モデルでデータアクセス

### マイグレーション & モデル作成
```bash
docker compose exec laravel-app-server php artisan make:model YourModel -m
# src/database/migrations/ のマイグレーションを編集
docker compose exec laravel-app-server php artisan migrate
```

### テストデータシード
```bash
docker compose exec laravel-app-server php artisan db:seed
# または特定の seeder: php artisan db:seed --class=ArticleSeeder
```

## デバッグ

### アプリケーションログ
- リアルタイム: `docker compose exec laravel-app-server php artisan pail`
- ファイル: `src/storage/logs/`

### データベース検査
- SQLite: `src/database/database.sqlite` に直接アクセス、または Tinker を使用
- クイエリ: `docker compose exec laravel-app-server php artisan tinker`

### コンテナシェル
- アクセス: `make app` または `docker compose exec laravel-app-server sh`

## 主要ファイルリファレンス
- モデル: `src/app/Models/`
- ルート: `src/routes/api.php`
- コントローラー: `src/app/Http/Controllers/Api/` （API コントローラーは `Api/` サブディレクトリに作成）
- 設定: `src/config/` （集約された設定ファイル）
- テスト: `src/tests/Feature/`、`src/tests/Unit/`
- マイグレーション: `src/database/migrations/`

---

## ドキュメント記述方針

このプロジェクトのドキュメント類は **日本語で統一** します。

### ドキュメント種別と格納先
- **`.github/copilot-instructions.md`** - AI エージェント向け指示書（日本語）
- **`docs/design.md`** - システム設計書（日本語）
- **`README.md`** - プロジェクト概要（日本語）

### 記述ルール
- すべてのドキュメントを **日本語** で作成
- マークダウン形式で統一
- コード例やファイルパスは英字のまま（変更しない）
- AI エージェントに指示を与える場合も日本語で記述

---

## コミットメッセージ規約

すべてのコミットメッセージは **日本語** で記述してください。

### フォーマット
```
<タイプ>: <説明>

<詳細（オプション）>
```

### タイプ
- `feat:` - 新機能追加
- `fix:` - バグ修正
- `refactor:` - リファクタリング
- `test:` - テスト追加・修正
- `docs:` - ドキュメント更新
- `chore:` - ビルド、依存関係など

### 例
```
feat: 記事検索機能を実装

- 記事タイトルでの検索
- コンテンツの部分検索
- ページネーション対応

fix: コメント投稿時のバリデーション修正

docs: API仕様書を更新

chore: Laravel Pint でコードを整形
```

### 説明部分のルール
- 動詞で始める（「〜を実装」「〜を修正」）
- 1行目は簡潔に（50字以下）
- 詳細は空行を開けて記述
- 関連する Issue/PR番号があれば記載

