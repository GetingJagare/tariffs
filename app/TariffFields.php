<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class TariffFields
 * @package App
 *
 * @property integer $id
 * @property string $name
 * @property integer $type_id
 */
class TariffFields extends Model
{
    protected $fillable = ['name', 'type_id'];

    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo('App\TariffFieldTypes');
    }
}
