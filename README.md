# 04-laravel-migration

Strangler Fig パターンで、在庫管理システムの **在庫一覧画面だけ** をレガシー
PHP 5.6 から Laravel 12(PHP 8.4)へ移行した時点のスナップショットです。
追加・入庫・出庫・削除は引き続きレガシー側が処理します。

## 構成

```
  host:8080 ─► reverse-proxy (nginx)
                  ├─ "/" , "/products"      ─►  laravel-web (PHP 8.4 / Laravel 12)  … 在庫一覧
                  └─ その他すべて (*.php)   ─►  legacy-web  (PHP 5.6)               … 追加/入出庫/削除
                                                      │
                        laravel-web ──────────────────┤
                                                      ▼
                                          legacy-mysql (MySQL 5.6 / DB: legacy_inventory)
                                          ※ 新旧で同一DBを共有

  4サービスすべてを外部ネットワーク  inventory-net  に接続
```

| ディレクトリ | 内容 |
|---|---|
| `www/` | レガシー在庫管理システム(PHP 5.6)。`php56/` がその Docker イメージ |
| `laravel/` | Laravel 12 アプリ(在庫一覧のみ実装)。`docker-php-base/` がその Docker 環境(PHP 8.4 + Apache) |
| `reverse-proxy/` | nginx リバースプロキシ。パスでレガシー / Laravel を振り分ける |
| `schema.sql` | `products` テーブル定義と初期データ(新旧で共有) |
| `characterization_tests.sh` | 特性化テスト(レガシー直アクセス) |
| `characterization_tests_via_proxy.sh` | 同上のリバースプロキシ経由版(在庫一覧は Laravel 側で検証) |
| `verify_race_condition.sh` | 在庫更新の競合状態(lost update)検証 |

## 起動方法

```bash
# 1. 新旧 compose が共有する外部ネットワークを作成(初回のみ)
docker network create inventory-net

# 2. レガシー側
docker compose up -d --build

# 3. Laravel 側
cd docker-php-base && docker compose up -d --build && cd ..

# 4. Laravel の初回セットアップ(laravel/ が空の場合のみ)
docker exec -w /var/www/html/laravel laravel-web composer create-project laravel/laravel . "^12.0"
#   → その後 laravel/.env の DB_* を legacy-mysql / legacy_inventory に向け、
#     php artisan key:generate を実行(詳細は docker-php-base/laravel_install.md)

# 5. リバースプロキシ
cd reverse-proxy && docker compose up -d && cd ..
```

- 統合エントリポイント(リバースプロキシ): `http://localhost:8080/`
- レガシー直アクセス(デバッグ用): `http://localhost:8082/`
- Laravel 直アクセス(デバッグ用): `http://localhost:8085/`
- Adminer: `http://localhost:8083/`(サーバー: `legacy-mysql` / ユーザー: `root` / パスワード: `.env` の `DB_PASSWORD`)

DB 接続情報は `.env`(gitignore 済み)から読み込みます。初回は `.env.example` をコピーして作成してください。

## テスト

```bash
# レガシー直アクセス版(10 checks)
docker compose exec legacy-web bash /var/www/characterization_tests.sh

# リバースプロキシ経由版(在庫一覧は Laravel 側で検証。10 checks)
docker compose exec legacy-web bash /var/www/characterization_tests_via_proxy.sh

# 在庫更新の競合状態の検証
docker compose exec legacy-web bash /var/www/verify_race_condition.sh 1 10 10
```

詳細は連載記事「レガシーPHPをAIエージェントと段階的にモダナイズする」を参照してください。
