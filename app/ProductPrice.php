<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductPrice extends Model
{
    protected $table = 'product_prices';

    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function product_group()
    {
        return $this->belongsTo(ProductGroup::class, 'product_id', 'product_id');
    }
}
