<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AlertStatus extends Model
{
    use SoftDeletes;
    protected $table    = "alert_status";
    protected $dates    =   ['deleted_at'];
    protected $guarded  =   [];
}
