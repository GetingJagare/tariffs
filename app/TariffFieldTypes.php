<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class TariffFieldTypes
 * @package App
 *
 * @property integer $id
 * @property string $name
 * @property string $alias
 */
class TariffFieldTypes extends Model
{
    protected $fillable = ['name', 'alias'];

    public $timestamps = false;
}
