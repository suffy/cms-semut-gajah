<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderDetail extends Model
{
    use SoftDeletes;
    protected $dates =['deleted_at'];

    protected $table = "order_detail";
    protected $guarded = [];

    public function order(){
        return $this->belongsTo('App\Order', 'order_id', 'id');
	}

    public function product(){
        return $this->belongsTo('App\Product', 'product_id', 'id');
	}

    public function review(){
        return $this->belongsTo('App\ProductReview', 'product_review_id', 'id');
	}

    public function promo(){
        return $this->belongsTo('App\Promo', 'promo_id', 'id');
	}
}
