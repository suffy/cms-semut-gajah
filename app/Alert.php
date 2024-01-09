<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Alert extends Model
{
    use SoftDeletes;
    protected $dates    =   ['deleted_at'];
    protected $guarded  =   [];

    public function user()
    {
        return $this->belongsToMany('App\User', 'alert_status', 'alert_id', 'user_id')->select('users.id');
    }
}
