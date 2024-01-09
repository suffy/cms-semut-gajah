<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;

class Product extends Model
{
    use SoftDeletes;
    protected $dates =['deleted_at'];

    protected $table = "products";
    protected $guarded = [];


    public function category()
	  {
		  return $this->hasOne('App\Category', 'id', 'category_id');
    }

    public function product_availability()
	  {
		  return $this->hasOne('App\ProductAvailability', 'product_id', 'id');
    }

    public function product_image()
	  {
		  return $this->hasMany('App\ProductImage', 'product_id');
    }

    public function recent_views()
    {
      return $this->hasMany('App\RecentProduct', 'product_id');
    }

    public function product_location()
    {
      return $this->hasMany('App\ProductLocation', 'product_id');
    }

    public function order()
	  {
		  return $this->hasMany('App\OrderDetail', 'product_id', 'id');
    }

    public function review()
	  {
		  return $this->hasMany('App\ProductReview', 'product_id', 'id');
    }

    public function complaint()
    {
      return $this->hasMany(Complaint::class, 'brand_id', 'id');
    }

    public function price()
    {
      return $this->hasOne(ProductPrice::class, 'product_id', 'id');
    }

    public function promo_reward()
    {
      return $this->hasMany('App\PromoReward', 'reward_product_id', 'id');
    }

    public function product_stock()
    {
      return $this->hasOne(ProductStock::class, 'product_id', 'id')->where('site_code', Auth::user()->site_code);
    }

    public function promo_sku()
    {
      return $this->hasMany('App\PromoSku', 'product_id', 'id');
    }

    public function cart()
    {
      return $this->hasOne('App\ShoppingCart', 'product_id', 'id');
    }

    public function top_spender()
    {
      return $this->hasMany('App\TopSpender', 'product_id', 'id');
    }
    
    public function mission_task()
	  {
		  return $this->hasMany('App\MissionTask', 'mission_id', 'id');
    }
}
