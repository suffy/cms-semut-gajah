<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BroadcastWADetail extends Model
{
    protected $table = 'broadcast_wa_detail';

    protected $guarded = [];

    function broadcast_wa() 
    {
        return $this->belongsTo(BroadcastWA::class, 'id_broadcast_wa', 'id');
    }

    function user()
    {
        return $this->belongsTo(User::class, 'send_to', 'id');
    }

    function distributor()
    {
        return $this->belongsTo(MappingSite::class, 'send_to', 'kode');
    }
}
