<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Tariffs
 * @package App
 *
 * @property int $id
 * @property string $name
 * @property Regions $region
 * @property int $region_id
 */
class Tariffs extends Model
{
    public $id;

    public $name;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function region()
    {
        return $this->hasOne('App\Regions');
    }
}
