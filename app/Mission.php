<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mission extends Model
{
    use SoftDeletes;
    protected $dates    =   ['deleted_at'];
    protected $guarded  =   [];

    public function tasks()
    {
        return $this->hasMany('App\MissionTask', 'mission_id', 'id')->orderBy('id', 'ASC');
    }
    
    public function user()
    {
        return $this->belongsToMany('App\User', 'mission_users', 'mission_id', 'user_id')->withPivot('mission_status');
    }
}
