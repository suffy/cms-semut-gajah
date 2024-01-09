<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RecentProduct extends Model
{
    protected $guarded = [];

    protected $table = 'recent_products';

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function product()
    {
        return $this->belongsTo('App\Product');
    }
}
