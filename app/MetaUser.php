<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MetaUser extends Model
{
    protected $table = 'user_meta';

    protected $guarded = [];

    public function salesman()
    {
        return $this->belongsTo('App\Salesman', 'salesman_code', 'kodesales');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'customer_code', 'customer_code');
    }
}
