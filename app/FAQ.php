<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FAQ extends Model
{
    use SoftDeletes;
    protected $dates =['deleted_at'];

    protected $table = "faqs";
    protected $guarded = [];

}
