<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductOffer extends Model
{
    use SoftDeletes;
    protected $dates =['deleted_at'];

    protected $table = "product_offers";
    protected $guarded = [];

    public function offer_item(){
        return $this->hasMany('App\ProductOfferItem', 'id', 'product_offer_id');
    }
}
