<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class TariffFieldValues
 * @package App
 *
 * @property integer $id
 * @property integer $tariffs_id
 * @property integer $tariff_fields_id
 * @property string $value
 * @property TariffFields $field
 */
class TariffFieldValues extends Model
{
    protected $fillable = ['tariffs_id', 'tariff_fields_id', 'value'];

    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function field()
    {
        return $this->belongsTo('App\TariffFields', 'tariff_fields_id');
    }
}
