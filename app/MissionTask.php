<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MissionTask extends Model
{
    use SoftDeletes;
    protected $dates    =   ['deleted_at'];
    protected $guarded  =   [];

    public function user()
    {
        return $this->belongsToMany('App\User', 'mission_task_users', 'mission_task_id', 'user_id')->withPivot('status');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'id');
    }

    public function mission()
    {
        return $this->belongsTo('App\Mission', 'mission_id');
    }
}
