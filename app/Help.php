<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Help extends Model
{
    protected $table = 'helps';

    protected $guarded = [];

    public function help_category()
    {
        return $this->belongsTo(HelpCategory::class, 'help_category_id', 'id');
    }
}
