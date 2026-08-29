<?php
// DB接続情報は環境変数から取得する(ソースコードに直書きしない)。
// 実際の値は docker-compose.yml 経由で .env から注入される。
$host = getenv('DB_HOST') ?: 'legacy-mysql';
$user = getenv('DB_USER') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: '';
$dbname = getenv('DB_NAME') ?: 'legacy_inventory';

// mysql_* 拡張は PHP 7 で削除済み。mysqli へ移行し、以降のクエリは
// プリペアドステートメントでバインドする(SQLインジェクション対策)。
$conn = mysqli_connect($host, $user, $pass, $dbname);
if (!$conn) {
    // 詳細(接続情報を含みうる mysqli_connect_error())は画面に出さない。
    die("DB接続に失敗しました");
}

mysqli_set_charset($conn, 'utf8');
?>
