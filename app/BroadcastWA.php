<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BroadcastWA extends Model
{
    protected $table = 'broadcast_wa';

    protected $guarded = [];
    protected $appends = ['type_name'];

    public function broadcast_wa_detail()
    {
        return $this->hasMany(BroadcastWADetail::class, 'id_broadcast_wa', 'id');
    }

    public function getTypeNameAttribute()
    {
        if($this->attributes['type'] == 'wa') {
            return 'WhatsApp';
        } else if($this->attributes['type'] == 'apps') {
            return 'Apps';
        }
    }
}
