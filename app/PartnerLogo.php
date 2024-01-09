<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PartnerLogo extends Model
{
    use SoftDeletes;
    protected $dates =['deleted_at'];

    protected $table = "partner_logo";
    protected $guarded = [];
}
