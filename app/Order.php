<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;
    protected $dates = ['deleted_at'];

    protected $table = "orders";
    protected $guarded = [];

    public function data_item()
    {
        return $this->hasMany('App\OrderDetail', 'order_id', 'id');
    }

    public function data_cancel()
    {
        return $this->hasMany('App\OrderDetail', 'order_id', 'id')->where('qty_cancel', '!=', 0);
    }
    
    public function data_success()
    {
        return $this->hasMany('App\OrderDetail', 'order_id', 'id')->where('qty_update', '!=', 0);
    }


    public function data_user()
    {
        return $this->hasOne('App\User', 'id', 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'customer_id', 'id');
    }

    public function data_user_order()
    {
        return $this->belongsTo('App\User', 'id', 'customer_id');
    }

    public function data_review()
    {
        return $this->hasMany('App\OrderDetail', 'order_id', 'id')->where('product_id', '!=', null)->where('product_review_id', '!=', null);
    }

    public function data_unreview()
    {
        return $this->hasMany('App\OrderDetail', 'order_id', 'id')->where('product_id', '!=', null)->where('product_review_id', null);
    }

    public function data_complaint()
    {
        return $this->hasMany(Complaint::class, 'order_id', 'id');
    }

    public function order_details()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'id')->where('product_id', '!=', null);
    }

    public function data_promo()
    {
        return $this->hasMany('App\OrderDetail', 'order_id', 'id')->where('product_id', null);
    }

    public function point_history()
    {
        return $this->hasOne('App\PointHistory', 'order_id', 'id');
    }
}
