<?php
require_once("db.php");

$id = isset($_GET['id']) ? $_GET['id'] : '';
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $qty = $_POST['qty'];

    // --- ここが本質的な問題箇所 ---
    // 1. 現在の在庫数をSELECTで読み取る
    // 2. PHP側で加算した値を計算する
    // 3. UPDATEで書き戻す
    // という3ステップが1つのトランザクションになっておらず、ロックも取っていない。
    // そのため、同時に複数のリクエストが来ると「更新の消失(lost update)」が発生する。
    //
    // 例: 在庫10個の商品に対し、同時に2つの入庫リクエスト(+5個ずつ)が来た場合
    //   リクエストA: SELECT → 10を読む
    //   リクエストB: SELECT → 10を読む(Aがまだ書き戻す前)
    //   リクエストA: UPDATE → 15に更新
    //   リクエストB: UPDATE → 15に更新(Aの更新を上書き)
    //   本来は20になるはずが、15のままになってしまう(+5個分が消える)

    $select_sql = "SELECT stock_quantity FROM products WHERE id = " . $id;
    $result = mysql_query($select_sql, $conn);
    $row = mysql_fetch_assoc($result);
    $current_qty = $row['stock_quantity'];

    $new_qty = $current_qty + intval($qty);

    // このSELECTとUPDATEの間に他のリクエストが割り込む可能性がある(競合状態)
    $update_sql = "UPDATE products SET stock_quantity = " . $new_qty . " WHERE id = " . $id;
    mysql_query($update_sql, $conn);

    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>入庫処理</title>
</head>
<body>
    <h1>入庫処理</h1>
    <form method="post" action="stock_in.php">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        入庫数: <input type="text" name="qty">
        <input type="submit" value="入庫する">
    </form>
    <p><a href="index.php">← 一覧に戻る</a></p>
</body>
</html>
