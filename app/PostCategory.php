<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PostCategory extends Model
{
    use SoftDeletes;
    protected $dates =['deleted_at'];

    protected $table = "post_categories";
    protected $guarded = [];

    public function sub_cat()
	{
		return $this->hasMany('App\PostCategory', 'category_parent', 'id');
    }

}