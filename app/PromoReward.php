<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PromoReward extends Model
{
    protected $table = "promo_rewards";
    protected $guarded = [];

    public function promo()
    {
        return $this->belongsTo('App\Promo', 'promo_id', 'id');
    }

    public function product()
    {
        return $this->belongsTo('App\Product', 'reward_product_id', 'id');
    }
}
