<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    //
    protected $table = 'logs';
    protected $guarded = [];

    public $timestamps = false;

    public function from_user_data()
	{
		return $this->belongsTo(User::class, 'from_user', 'id');
    }
    
    public function to_user_data()
	{
		return $this->belongsTo(User::class, 'to_user', 'id');
	}

    public function transaction($data){

        $activity = null;
        $table_id = null;
        $from_user = null;
        $to_user = null;
        $admin_seen = null;
        $user_seen = null;

        if(isset($data['table_id'])){
            $table_id = $data['table_id'];
        }
        if(isset($data['activity'])){
            $activity = $data['activity'];
        }
        if(isset($data['from_user'])){
            $from_user = $data['from_user'];
        }
        if(isset($data['user_seen'])){
            $user_seen = $data['user_seen'];
        }
        if(isset($data['admin_seen'])){
            $admin_seen = $data['admin_seen'];
        }

        $sql = Log::create([
            'log_time' =>date('Y-m-d H:i:s'),
            'activity'=> $activity,
            'table_name' => 'orders',
            'table_id' => $table_id,
            'from_user'  => $from_user,
            'to_user' => $to_user,
            'admin_seen' => $admin_seen,
            'user_seen' => $user_seen,
            'status' => 0
        ]);

        return $sql;
    }

    public function time_elapsed_string($datetime, $full = false) {
        $now = new \DateTime();
        $ago = new \DateTime($datetime);
        $diff = $now->diff($ago);

        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;

        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }

        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }

}
