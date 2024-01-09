<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CreditHistory extends Model
{
    protected $table = "credit_histories";
    protected $guarded = [];

    public function credit_data()
    {
        return $this->belongsTo('App\Credit', 'credit_id', 'id');
    }

    public function order()
    {
        return $this->belongsTo('App\Order', 'order_id', 'id');
    }
}
