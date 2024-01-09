<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subscribe extends Model
{
    use SoftDeletes;
    protected $dates =['deleted_at'];

    protected $table = "subscribes";
    protected $guarded = [];

    public function user()
    {
      return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function product()
    {
      return $this->belongsTo(Product::class, 'product_id', 'id');
    }
}
