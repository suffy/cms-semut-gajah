<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserAddress extends Model
{
    // use SoftDeletes;
    // protected $dates =['deleted_at'];

    protected $table = "user_address";
    protected $guarded = [];

    public function user()
	  {
      return $this->hasOne('App\User', 'user_id', 'id');
    }

    // public function users()
    // {
    // 	return $this->belongsTo('App\User');
    // }

    // public function province()
    // {
		// return $this->hasOne('App\LocalProvince', 'province_id', 'provinsi');
    // }

    // public function city()
    // {
		// return $this->hasOne('App\LocalCities', 'city_id', 'kota');
    // }

    // public function subdistrict()
    // {
		// return $this->hasOne('App\LocalSubDistrict', 'subdistrict_id', 'kecamatan');
    // }

    public function site_name()
    {
    	return $this->belongsTo('App\MappingSite', 'mapping_site_id', 'id');
    }
}
