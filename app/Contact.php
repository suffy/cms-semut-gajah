<?php

namespace App;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use SoftDeletes;
    protected $dates =['deleted_at'];

    protected $table = "contacts";
    protected $guarded = [];
}
