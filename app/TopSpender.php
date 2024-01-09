<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TopSpender extends Model
{
    use SoftDeletes;
    protected $dates =['deleted_at'];
    protected $table = "top_spender";
    protected $guarded = [];
    protected $appends = ["filter", "hadiah"];

    public function winner()
    {
        return $this->hasMany('App\TopSpenderWinner', 'top_spender_id', 'id');
    }

    public function rank_reward()
    {
        return $this->hasMany('App\TopSpenderReward', 'top_spender_id', 'id');
    }

    public function site_name()
    {
    	return $this->belongsTo('App\MappingSite', 'site_code', 'kode');
    }

    public function product()
    {
        return $this->belongsTo('App\Product', 'product_id', 'id');
	}

    public function histories()
    {
        return $this->hasMany('App\PointHistory', 'top_spender_id', 'id');
    }

    public function getFilterAttribute()
    {
        // $text = [];
        $text = '';
        if($this->site_code != null) {
            // array_push($text, 'Site Code: ' . $this->site_code);
            $text .= 'Site Code: ' . $this->site_code . '|';
        }
        if($this->brand_id != null) {
            // array_push($text, 'Brand: ' . $this->brand->name);
            $text .= 'Brand: ' . $this->brand_id . '|';
        }
        if($this->product_id != null) {
            // array_push($text, 'Product: ' . $this->product->name);
            $text .= 'Product: ' . $this->product_id . '|';
        }
        if($text == '') {
            return 'Tidak ada filter';
        } else {
            return $text;
        }
    }

    public function getHadiahAttribute()
    {
        if($this->reward == 'cash') {
            return 'Rp' . number_format($this->nominal, 0, ',', '.');
        } else if($this->reward == 'point') {
            return number_format($this->nominal, 0, ',', '.') . ' Point';
        }
    }
}
