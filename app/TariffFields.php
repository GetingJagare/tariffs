<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class TariffFields
 * @package App
 *
 * @property integer $id
 * @property string $name
 * @property integer $tariff_field_types_id
 * @property TariffFieldTypes $type
 */
class TariffFields extends Model
{
    protected $fillable = ['name', 'tariff_field_types_id'];

    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo('App\TariffFieldTypes', 'tariff_field_types_id');
    }
}
