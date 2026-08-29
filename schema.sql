SET NAMES utf8;

CREATE DATABASE IF NOT EXISTS legacy_inventory DEFAULT CHARACTER SET utf8;
USE legacy_inventory;

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sku VARCHAR(50) NOT NULL,
    name VARCHAR(255) NOT NULL,
    price INT NOT NULL DEFAULT 0,
    stock_quantity INT NOT NULL DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO products (sku, name, price, stock_quantity) VALUES
    ('SKU-001', 'コピー用紙 A4', 500, 120),
    ('SKU-002', 'ボールペン(黒)', 100, 8),
    ('SKU-003', 'ファイルボックス', 800, 15),
    ('SKU-004', 'ホッチキス', 600, 3),
    ('SKU-005', '付箋メモ', 250, 40);
