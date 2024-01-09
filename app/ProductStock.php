<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductStock extends Model
{
    protected $table = 'product_stocks';

    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class, 'id', 'product_id');
    }
}
