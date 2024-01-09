<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TopSpenderReward extends Model
{
    use SoftDeletes;
    protected $dates =['deleted_at'];
    protected $table = "top_spender_reward";
    protected $guarded = [];

    public function top_spender()
    {
        return $this->belongsTo('App\TopSpender', 'top_spender_id', 'id');
    }
}
