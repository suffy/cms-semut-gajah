<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Delivery extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\User', 'customer_code', 'customer_code');
    }
}
