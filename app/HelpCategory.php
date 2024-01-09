<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class HelpCategory extends Model
{
    protected $table = 'help_categories';

    protected $guarded = [];

    public function help()
    {
        return $this->hasMany(Help::class, 'id', 'help_category_id');
    }
}
