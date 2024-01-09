<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    protected $table = 'complaints';

    protected $guarded = [];

    public function order()
    {
        return $this->hasOne(Order::class, 'id', 'order_id');
    }

    public function order_details()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'brand_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function credits()
    {
        return $this->hasMany('App\Credit', 'complaint_id', 'id');
    }

    public function complaint_detail()
    {
        return $this->hasMany(ComplaintDetail::class, 'complaint_id', 'id');
    }

    public function complaint_file()
    {
        return $this->hasMany(ComplaintFile::class, 'complaint_id', 'id');
    }
}
