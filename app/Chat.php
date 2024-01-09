<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $table = 'chats';
    protected $guarded = [];

    public function to_user()
    {
        return $this->belongsTo(User::class, 'to_id', 'id');
    }
}
