# 01-legacy-baseline

「現状診断編」の時点でのスナップショットです。意図的にレガシーな在庫管理システムと、その環境構築一式が含まれます。

## 起動方法

```bash
cd legacy-docker
docker-compose up -d --build
```

- 在庫管理システム: `http://localhost:8082/`
- Adminer(DB管理画面): `http://localhost:8083/`(サーバー: `legacy-mysql`、ユーザー: `root`、パスワード: `my-secret-pw`)

## 含まれるもの

- `legacy-docker/` — PHP 5.6 + MySQL 5.6 のDocker環境と、意図的にアンチパターンを仕込んだ在庫管理システム本体

詳細は連載記事「レガシーPHPをAIエージェントと段階的にモダナイズする(1) 現状診断編」を参照してください。
