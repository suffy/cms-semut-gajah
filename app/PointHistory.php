<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PointHistory extends Model
{
    protected $table = "point_histories";
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\User', 'customer_id', 'id');
    }
    
    public function order()
    {
        return $this->belongsTo('App\Order', 'order_id', 'id');
    }

    public function topSpender()
    {
        // return $this->belongsTo('App\Order', 'order_id', 'id');
        return $this->belongsTo('App\TopSpender', 'top_spender_id', 'id');
    }
}
