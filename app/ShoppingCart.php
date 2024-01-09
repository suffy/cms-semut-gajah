<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShoppingCart extends Model
{
    use SoftDeletes;
    protected $dates =['deleted_at'];

    protected $table = "shopping_cart";
    protected $guarded = [];

    public function data_product()
    {
      return $this->belongsTo('App\Product', 'product_id', 'id');
    }
}
