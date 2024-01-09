<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StatCounter extends Model
{
    //
    protected $table = 'stat_counters';
    protected $guarded = [];

    public $timestamps = false;

}

