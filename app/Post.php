<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;
    protected $dates =['deleted_at'];

    protected $table = "posts";
    protected $guarded = [];


    public function category()
	{
		return $this->hasOne('App\PostCategory', 'id', 'post_category_id');
    }

    public function author()
	{
		return $this->hasOne('App\User', 'id', 'user_id');
	}
}
