<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feature extends Model
{
    use SoftDeletes;
    protected $dates =['deleted_at'];

    protected $table = "features";
    protected $guarded = [];

    public function category(){
		return $this->hasOne('App\Category', 'id', 'category_id');
	}
}
