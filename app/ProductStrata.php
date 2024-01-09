<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductStrata extends Model
{
    protected $table = "product_strata";
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo('App\Product', 'product_id', 'id');
    }
}
