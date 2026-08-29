# legacy-inventory-to-laravel

レガシーPHP(手続き型・PHP 5.6相当)の在庫管理システムを、AIエージェント(Claude Code)と一緒に段階的にモダナイズしていくプロジェクトです。Qiitaの連載記事と対応しています。

## この連載について

| # | ブランチ | 記事 | 内容 |
|---|---|---|---|
| 1 | [`01-legacy-baseline`](../../tree/01-legacy-baseline) | 現状診断編 | あえてレガシーに作った在庫管理システムと、技術的負債の棚卸し |
| 2 | `02-safety-net` | セーフティネット構築編 | リファクタ前に、壊れていないことを保証するテストを書く |
| 3 | `03-refactor` | 段階的リファクタリング編 | 小さいステップで安全に直していく |
| 4 | `04-laravel-migration` | モダンフレームワークへの移行編 | Strangler Figパターン的にLaravelへ移行する |

各ブランチは、その記事の時点でのソースコードのスナップショットになっています。`main`ブランチはこのREADMEのみを管理し、実際のコードは各番号付きブランチにあります。

## 使い方

読みたい回のブランチをチェックアウトしてください。

```bash
git clone https://github.com/<your-account>/legacy-inventory-to-laravel.git
cd legacy-inventory-to-laravel
git checkout 01-legacy-baseline
```

各ブランチのREADME(またはルートのdocker-compose.yml)に、その回の環境構築手順があります。

## 題材

商品の一覧・追加・入庫・出庫・削除ができる、小さな在庫管理システムです。「同時に複数人が同じ商品の在庫を更新したらどうなるか」という、実務でありがちな問題を軸に、意図的にレガシーな実装(SQLインジェクション・XSS・CSRF・競合状態など)を仕込んでいます。