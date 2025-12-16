# 変数定義
SRC_DIR := src
APP_SERVER := laravel-app-server
WEB_SERVER := laravel-web-server
DB_SERVER := laravel-db-server

# Makefileで定義する独自コマンド
.PHONY: setup build up stop start down down-v destroy restart app serve tinker migrate migrate-reset seed cache-clear config-clear optimize-clear log pint test sqlite

# Laravelプロジェクトの新規作成
setup:
	@if [ ! -d $(SRC_DIR)/vendor ]; then \
		make up; \
		docker compose cp ./docker-config/php/.env.laravel $(APP_SERVER):/var/www/html/.env; \
		docker compose exec $(APP_SERVER) composer install; \
		docker compose exec $(APP_SERVER) php artisan key:generate; \
		docker compose exec $(APP_SERVER) php artisan migrate; \
		docker compose exec $(APP_SERVER) chmod -R 777 storage bootstrap/cache; \
		docker compose exec $(APP_SERVER) chown -R laravel:laravel /var/www/html; \
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
web:
	docker compose exec $(WEB_SERVER) sh
db:
	docker compose exec $(DB_SERVER) sh
mysql:
	docker compose exec $(DB_SERVER) mysql -u root -p

# Laravel関連コマンド
serve:
	docker compose exec -d $(APP_SERVER) php artisan serve --host 0.0.0.0 --port 8000
tinker:
	docker compose exec $(APP_SERVER) php artisan tinker
migrate:
	docker compose exec $(APP_SERVER) php artisan migrate
migrate-reset:
	docker compose exec $(APP_SERVER) php artisan migrate:reset
seed:
	docker compose exec $(APP_SERVER) php artisan db:seed
cache-clear:
	docker compose exec $(APP_SERVER) php artisan cache:clear
config-clear:
	docker compose exec $(APP_SERVER) php artisan config:clear
optimize-clear:
	docker compose exec $(APP_SERVER) php artisan optimize:clear
log:
	docker compose exec $(APP_SERVER) tail -f storage/logs/laravel.log
pint:
	docker compose exec $(APP_SERVER) ./vendor/bin/pint
test:
	docker compose exec $(APP_SERVER) php artisan test
sqlite:
	docker compose exec $(APP_SERVER) sqlite3 database/database.sqlite
