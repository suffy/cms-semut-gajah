<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Wishlist extends Model
{
    use SoftDeletes;
	protected $dates =['deleted_at'];

	protected $table = "wishlists";
	protected $guarded = [];


	public function product()
	{
		return $this->hasOne('App\Product', 'id', 'product_id');
	}
	
	public function review()
	{
		return $this->hasMany('App\ProductReview', 'product_id', 'id');
    }
}
