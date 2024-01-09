<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductOfferItem extends Model
{
    //
    protected $table = 'product_offer_item';
    protected $guarded = [];

    use SoftDeletes;
    protected $dates =['deleted_at'];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
     public function product_location()
    {
        return $this->belongsTo(ProductLocation::class);
    }
}
