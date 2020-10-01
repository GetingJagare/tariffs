<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TariffFieldTypes extends Model
{
    protected $fillable = ['name', 'alias'];

    public $timestamps = false;
}
