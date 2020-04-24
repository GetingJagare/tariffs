<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Regions
 * @package App
 *
 * @property int $id
 * @property string $name
 * @property string $region_center
 */
class Regions extends Model
{

    protected $fillable = ['name'];

}
