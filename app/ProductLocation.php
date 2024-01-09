<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductLocation extends Model
{
    //
    protected $table = 'product_location';
    protected $guarded = [];

    public function location(){
        return $this->hasOne('App\Location', 'id', 'location_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

}
