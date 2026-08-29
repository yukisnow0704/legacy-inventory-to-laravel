<?php
// DB接続情報がソースコードに直書き(ハードコード)されている。
// 本番用の認証情報がgit管理下のファイルにそのまま残ってしまうアンチパターン。
$host = "legacy-mysql";
$user = "root";
$pass = "my-secret-pw";
$dbname = "legacy_inventory";

$conn = mysql_connect($host, $user, $pass);
if (!$conn) {
    // エラーメッセージに接続情報が漏れる可能性があり、かつ die()で処理が止まるだけで
    // ログにも残らない。
    die("DB接続に失敗しました: " . mysql_error());
}

mysql_select_db($dbname, $conn);
mysql_query("SET NAMES utf8", $conn);
?>
