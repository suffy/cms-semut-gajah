<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Credit extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\User', 'customer_code', 'customer_code');
    }

    public function complaint()
    {
        return $this->belongsTo('App\Complaint', 'complaint_id', 'id');
    }

    public function histories()
    {
        return $this->hasMany('App\CreditHistory', 'credit_id', 'id');
    }
}
