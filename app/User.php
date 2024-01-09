<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Tymon\JWTAuth\Contracts\JWTSubject;


class User extends Authenticatable implements JWTSubject
{
    use Notifiable;

    // use SoftDeletes;
    // protected $dates =['deleted_at'];

    protected $table = "users";
    protected $guarded = [];

    public function user_address()
    {
    	return $this->hasMany('App\UserAddress', 'user_id', 'id');
    }

    public function verification()
    {
    	return $this->hasOne('App\NotificationVerification', 'user_id', 'id');
    }

    public function recent_view_products()
    {
        return $this->hasMany('App\RecentProduct',' user_id', 'id');
    }

    public function user_default_address()
    {
    	return $this->hasMany('App\UserAddress')->where('default_address', '!=', null);
    	// return $this->hasMany('App\UserAddress');
    }

    public function user_order()
    {
    	return $this->hasMany('App\Order');
    }

    public function user_subscribe()
    {
    	return $this->hasMany('App\Subscribe', 'user_id', 'id');
    }

    public function site_name()
    {
    	return $this->belongsTo('App\MappingSite', 'site_code', 'kode');
    }

    public function salesman()
    {
        return $this->belongsTo('App\Salesman', 'salesman_code', 'kodesales');
    }

    public function meta_user()
    {
        return $this->hasMany('App\MetaUser', 'customer_code', 'customer_code');
    }

    public function product_review()
    {
        return $this->hasMany(ProductReview::class, 'id', 'user_id');
    }

    public function user_complaint()
    {
        return $this->hasMany(Complaint::class, 'user_id', 'id');
    }

    public function to_user_chat()
    {
        return $this->hasMany(Chat::class, 'id', 'toId');
    }

    public function credit_limits()
    {
        return $this->hasMany(CreditLimit::class, 'customer_code', 'customer_code');
    }

    public function credits()
    {
        return $this->hasMany('App\Credit', 'customer_id', 'id');
    }

    public function point_histories()
    {
        return $this->hasMany('App\PointHistory', 'customer_id', 'id');
    }

    public function user_feedback()
    {
        return $this->hasMany(Feedback::class, 'user_id', 'id');
    }

    public function broadcast_wa_detail()
    {
        return $this->hasMany(BroadcastWADetail::class, 'send_to', 'id');
    }

    public function winner()
    {
        return $this->hasMany('App\TopSpenderWinner', 'customer_id', 'id');
    }

    public function delivery()
    {
        return $this->hasOne('App\Delivery', 'customer_code', 'customer_code');
    }

    public function task()
    {
        return $this->belongsToMany('App\MissionTask', 'mission_task_users', 'user_id', 'mission_task_id')->withPivot('status');
    }

    public function alert()
    {
        return $this->belongsToMany('App\Alert', 'alert_status', 'alert_id', 'user_id');
    }

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

}
