# 変数定義
SRC_DIR := src
SRC_REF_DIR := src_ref
APP_SERVER := laravel-app-server
REF_APP_SERVER := ref-app-server

# Makefileで定義する独自コマンド
.PHONY: setup build up stop start down down-v destroy restart app ref serve ref-serve

# Laravelプロジェクトの新規作成
setup:
	@if [ ! -d $(SRC_DIR)/vendor ]; then \
		mkdir $(SRC_DIR); \
		cp ./docker-config/php/.env.laravel ./$(SRC_REF_DIR)/.env; \
		make up; \
		docker compose exec $(APP_SERVER) composer create-project --prefer-dist "laravel/laravel=12.*" .; \
		docker compose cp ./docker-config/php/.env.laravel $(APP_SERVER):/var/www/html/.env; \
		docker compose exec $(APP_SERVER) php artisan key:generate; \
		docker compose exec $(APP_SERVER) php artisan migrate; \
		docker compose exec $(APP_SERVER) chmod -R 777 storage bootstrap/cache; \
		docker compose exec $(APP_SERVER) chown -R laravel:laravel /var/www/html; \
		docker compose cp ./docker-config/php/.env.laravel $(REF_APP_SERVER):/var/www/html/.env; \
		docker compose exec $(REF_APP_SERVER) composer install; \
		docker compose exec $(REF_APP_SERVER) php artisan key:generate; \
		docker compose exec $(REF_APP_SERVER) php artisan migrate; \
		docker compose exec $(REF_APP_SERVER) chmod -R 777 storage bootstrap/cache; \
		docker compose exec $(REF_APP_SERVER) chown -R laravel:laravel /var/www/html; \
	else \
		echo "-> Laravelプロジェクトが存在するため、インストールをスキップしました。"; \
	fi

# docker-compose基本コマンド
build:
	docker compose build
up:
	docker compose up -d
stop:
	docker compose stop
start:
	docker compose start
down:
	docker compose down --remove-orphans
down-v:
	docker compose down --remove-orphans -v
destroy:
	docker compose down --remove-orphans -v --rmi all
restart:
	@make down
	@make up

# コンテナログイン
app:
	docker compose exec $(APP_SERVER) sh
ref:
	docker compose exec $(REF_APP_SERVER) sh

# Laravel開発サーバ起動
serve:
	docker compose exec -d $(APP_SERVER) php artisan serve --host 0.0.0.0 --port 8000
ref-serve:
	docker compose exec -d $(REF_APP_SERVER) php artisan serve --host 0.0.0.0 --port 8000
