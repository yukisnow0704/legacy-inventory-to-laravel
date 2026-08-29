<?php
require_once("db.php");

// GETリクエストだけで削除が実行されてしまう(本来は POST + CSRFトークンが必要)。
// 認証・認可のチェックも一切ない(誰でもURLを知っていれば削除できる)。
$id = isset($_GET['id']) ? $_GET['id'] : '';

// IDをそのままSQLに連結している(SQLインジェクション脆弱性)
$sql = "DELETE FROM products WHERE id = " . $id;
mysql_query($sql, $conn);

header("Location: index.php");
exit;
?>
