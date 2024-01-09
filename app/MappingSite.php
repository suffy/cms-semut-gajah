<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class MappingSite extends Model
{
    use SoftDeletes;
    protected $dates =['deleted_at'];

    protected $table = "mapping_site";
    protected $guarded = [];

    public function user()
    {
    	return $this->hasMany('App\User', 'id', 'site_id');
    }

    public function top_spender()
    {
        return $this->hasMany('App\TopSpender', 'site_code', 'kode');
    }

    public function product_availability()
    {
    	return $this->hasOne('App\ProductAvailability', 'id', 'site_code');
    }

    public function user_address()
    {
        return $this->hasMany('App\UserAddress', 'id', 'mapping_site_id')
                    ->where('mapping_site_id', '!=', null);
    }

    public function broadcast_wa_detail()
    {
        return $this->hasMany('App\BroadcastWADetail', 'send_to', 'kode');
    }

    public function ho_child()
    {
        return $this->hasMany('App\MappingSite', 'sub', 'sub');
    }
}
