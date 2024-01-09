<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductReview extends Model
{
    use SoftDeletes;
    protected $dates =['deleted_at'];

    protected $table = "product_review";
    protected $guarded = [];

    public function product()
    {
        return $this->belongsTo('App\Product', 'product_id', 'id');
    }

    public function order_detail()
    {
        return $this->hasOne('App\OrderDetail', 'product_review_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
