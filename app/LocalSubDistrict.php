<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class LocalSubDistrict extends Model
{
    // use SoftDeletes;
    protected $dates =['deleted_at'];

    protected $table = "tb_ro_subdistricts";
    protected $guarded = [];
}
