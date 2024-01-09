<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Menu extends Model
{
    use SoftDeletes;
    protected $dates =['deleted_at'];

    protected $table = "menus";
    protected $guarded = [];

    public function category(){
		  return $this->hasMany('App\Category', 'id', 'menu_id');
	}
}
