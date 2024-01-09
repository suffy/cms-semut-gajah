<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CreditLimit extends Model
{
    protected $table = 'credit_limits';

    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'customer_code', 'customer_code');
    }
}
