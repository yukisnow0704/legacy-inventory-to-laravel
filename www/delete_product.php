<?php
require_once("db.php");

// GET だけで削除できる点・認証が無い点は今回のスコープ外(フェーズ1)。
$id = isset($_GET['id']) ? $_GET['id'] : '';

// ID はプレースホルダでバインドする(SQLインジェクション対策)。
$stmt = mysqli_prepare($conn, "DELETE FROM products WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);

header("Location: index.php");
exit;
?>
