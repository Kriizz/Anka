<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Lang extends Model
{
    protected $table = 'langs';

    protected $connection = 'intel';

    public $timestamps = false;
}
