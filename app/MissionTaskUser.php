<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MissionTaskUser extends Model
{
    protected $table    = 'mission_task_users';
    public $timestamps  = false;
    protected $guarded  = [];
}
