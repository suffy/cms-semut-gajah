<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ProductAvailability extends Model
{
    protected $dates =['deleted_at'];

    protected $table = "product_availability";
    protected $guarded = [];
    
    public function product()
    {
        return $this->belongsTo('App\Product', 'product_id', 'id');
    }

    public function mapping_site()
    {
        return $this->belongsTo('App\MappingSite', 'id', 'site_code');
    }
}
