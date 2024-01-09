<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Otp extends Model
{
    protected $dates =['created_at', 'updated_at', 'verified_at', 'valid_until'];
    
    protected $table = 'otp';
    protected $guarded = [];

}
