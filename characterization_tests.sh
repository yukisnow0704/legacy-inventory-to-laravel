#!/usr/bin/env bash
# 特性化テスト(Characterization Test)スイート。
#
# 「今のコードが実際にどう動いているか」をそのまま記録するテストです。
# バグも含めて現状の挙動を固定し、この後のリファクタリングで
# 意図しない変化が起きていないかを検知するためのセーフティネットにします。
# (「正しい仕様」を検証するテストではない点に注意してください)
#
# ★ このスクリプトは legacy-web コンテナの「中」で実行する前提です。
#
# 使い方(ホスト側のターミナル/PowerShellから):
#   docker-compose exec legacy-web bash /var/www/characterization_tests.sh

set -u

BASE_URL="http://localhost"
DB_HOST="legacy-mysql"
DB_USER="root"
DB_PASS="my-secret-pw"
DB_NAME="legacy_inventory"

PASS=0
FAIL=0

mysql_query() {
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" "$DB_NAME" -N -e "$1" 2>/dev/null
}

# 各テストの前提を揃えるため、既知の状態にDBをリセットする
reset_db() {
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" -e "DROP DATABASE IF EXISTS ${DB_NAME};" 2>/dev/null
    mysql -h "$DB_HOST" -u "$DB_USER" -p"$DB_PASS" < /var/www/schema.sql 2>/dev/null
}

assert_contains() {
    local haystack="$1"
    local needle="$2"
    local test_name="$3"
    if echo "$haystack" | grep -qF -- "$needle"; then
        echo "  [PASS] ${test_name}"
        PASS=$((PASS + 1))
    else
        echo "  [FAIL] ${test_name}"
        echo "         期待した文字列が見つかりません: ${needle}"
        FAIL=$((FAIL + 1))
    fi
}

assert_equals() {
    local actual="$1"
    local expected="$2"
    local test_name="$3"
    if [ "$actual" = "$expected" ]; then
        echo "  [PASS] ${test_name}"
        PASS=$((PASS + 1))
    else
        echo "  [FAIL] ${test_name}"
        echo "         期待値: ${expected} / 実際: ${actual}"
        FAIL=$((FAIL + 1))
    fi
}

echo "=== DBを既知の状態にリセット ==="
reset_db
echo "リセット完了(初期5商品)"
echo ""

echo "=== テスト1: index.php - 商品一覧が表示される ==="
BODY=$(curl -s "${BASE_URL}/index.php")
assert_contains "$BODY" "在庫一覧" "タイトルが表示される"
assert_contains "$BODY" "コピー用紙 A4" "初期データの商品名が表示される"
assert_contains "$BODY" "残りわずか" "在庫5個以下の商品に警告表示が出る(ボールペン:8個は対象外、ホッチキス:3個は対象)"
echo ""

echo "=== テスト2: index.php - キーワード検索 ==="
BODY=$(curl -s -G "${BASE_URL}/index.php" --data-urlencode "keyword=ボールペン")
assert_contains "$BODY" "ボールペン" "検索結果に一致商品が含まれる"
echo ""

echo "=== テスト3: add_product.php - 新規商品追加 ==="
curl -s -X POST "${BASE_URL}/add_product.php" \
    -d "sku=TEST-999&name=特性化テスト商品&price=999&stock_quantity=50" \
    -o /dev/null
BODY=$(curl -s "${BASE_URL}/index.php")
assert_contains "$BODY" "特性化テスト商品" "追加した商品が一覧に表示される"
NEW_ID=$(mysql_query "SELECT id FROM products WHERE sku = 'TEST-999';")
echo "  (追加された商品ID: ${NEW_ID})"
echo ""

echo "=== テスト4: stock_in.php - 入庫すると在庫が増える ==="
BEFORE=$(mysql_query "SELECT stock_quantity FROM products WHERE id = 1;")
curl -s -X POST "${BASE_URL}/stock_in.php" -d "id=1&qty=5" -o /dev/null
AFTER=$(mysql_query "SELECT stock_quantity FROM products WHERE id = 1;")
assert_equals "$AFTER" "$((BEFORE + 5))" "在庫が入庫数ぶん増える(${BEFORE} → $((BEFORE + 5)))"
echo ""

echo "=== テスト5: stock_out.php - 出庫すると在庫が減る(在庫が十分な場合) ==="
BEFORE=$(mysql_query "SELECT stock_quantity FROM products WHERE id = 2;")
curl -s -X POST "${BASE_URL}/stock_out.php" -d "id=2&qty=3" -o /dev/null
AFTER=$(mysql_query "SELECT stock_quantity FROM products WHERE id = 2;")
assert_equals "$AFTER" "$((BEFORE - 3))" "在庫が出庫数ぶん減る(${BEFORE} → $((BEFORE - 3)))"
echo ""

echo "=== テスト6: stock_out.php - 在庫不足時はエラーになり、在庫は変化しない ==="
# id=4(ホッチキス)は初期在庫3個。999個の出庫は不可能なはず
BEFORE=$(mysql_query "SELECT stock_quantity FROM products WHERE id = 4;")
BODY=$(curl -s -X POST "${BASE_URL}/stock_out.php" -d "id=4&qty=999")
AFTER=$(mysql_query "SELECT stock_quantity FROM products WHERE id = 4;")
assert_contains "$BODY" "在庫が不足しています" "在庫不足のエラーメッセージが表示される"
assert_equals "$AFTER" "$BEFORE" "在庫数が変化しない(${BEFORE}のまま)"
echo ""

echo "=== テスト7: delete_product.php - 削除すると一覧から消える ==="
DELETE_ID=$(mysql_query "SELECT id FROM products WHERE sku = 'TEST-999';")
curl -s "${BASE_URL}/delete_product.php?id=${DELETE_ID}" -o /dev/null
BODY=$(curl -s "${BASE_URL}/index.php")
if echo "$BODY" | grep -qF "特性化テスト商品"; then
    echo "  [FAIL] 削除した商品が一覧から消えていない"
    FAIL=$((FAIL + 1))
else
    echo "  [PASS] 削除した商品が一覧から消えている"
    PASS=$((PASS + 1))
fi
echo ""

echo "=================================="
echo "結果: ${PASS} passed, ${FAIL} failed"
echo "=================================="

if [ "$FAIL" -gt 0 ]; then
    exit 1
fi