<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes;
    protected $table = 'categories';
    protected $guarded = [];

    public $timestamps = false;


    public function menu(){
        return $this->belongsTo(Menu::class);
    }

    public function feature(){
        return $this->hasMany(Feature::class);
    }

    public function product(){
        return $this->hasMany(Product::class);
    }
    
}
