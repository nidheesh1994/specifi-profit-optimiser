<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'name',
        'quantity',
        'category',
        'trade_price',
        'retail_price',
        'mpn',
        'sku',
    ];
}
