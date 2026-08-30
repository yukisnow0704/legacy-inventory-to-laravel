<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * レガシーの products テーブル(schema.sql)にそのまま対応するモデル。
 * 既存スキーマには updated_at が無く、今回は読み取り専用なので
 * タイムスタンプ自動管理は無効にする。
 */
class Product extends Model
{
    protected $table = 'products';

    public $timestamps = false;

    protected $guarded = [];
}
