<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Penawaran extends Model
{
    //
    protected $table = 'product_offers';
    protected $guarded = [];

    use SoftDeletes;
    protected $dates =['deleted_at'];

    public function item()
    {
        return $this->hasMany(PenawaranItem::class);
    }

}
