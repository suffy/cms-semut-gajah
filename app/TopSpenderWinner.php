<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TopSpenderWinner extends Model
{
    use SoftDeletes;
    protected $dates =['deleted_at'];
    protected $table = "top_spender_winner";
    protected $guarded = [];

    public function top_spender()
    {
        return $this->belongsTo('App\TopSpender', 'top_spender_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo('App\User', 'customer_id', 'id');
    }
}
