<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ComplaintFile extends Model
{
    protected $table = 'complaint_files';

    protected $guarded = [];

    public function complaint()
    {
        return $this->belongsTo(Complaint::class, 'id', 'complaint_id');
    }

    public function complaint_detail()
    {
        return $this->belongsTo(ComplaintDetail::class, 'complaint_detail_id', 'id');
    }
}
