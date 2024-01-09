<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Staudenmeir\EloquentEagerLimit\HasEagerLimit;

class PromoSku extends Model
{
    use HasEagerLimit;
    protected $table = "promo_skus";
    protected $guarded = [];

    public function promo()
    {
        return $this->belongsTo('App\Promo', 'promo_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo('App\Product', 'product_id', 'id');
    }
}
