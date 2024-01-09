<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Location extends Model
{
    //
    protected $table = 'locations';
    protected $guarded = [];

    use SoftDeletes;
    protected $dates =['deleted_at'];

}

