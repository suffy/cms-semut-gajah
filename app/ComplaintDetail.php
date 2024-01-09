<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ComplaintDetail extends Model
{
    protected $table = 'complaint_details';

    protected $guarded = [];

    public function complaint()
    {
        return $this->belongsTo(Complaint::class, 'complaint_id', 'id');
    }

    public function complaint_file()
    {
        return $this->hasMany(ComplaintFile::class, 'complaint_detail_id', 'id');
    }
}
