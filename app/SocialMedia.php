<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SocialMedia extends Model

{

    protected $dates =['deleted_at'];

    protected $table = "social_media";
    protected $guarded = [];


}
