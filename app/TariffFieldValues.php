<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TariffFieldValues extends Model
{
    protected $fillable = ['field_id', 'tariff_id', 'value'];

    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function field()
    {
        return $this->belongsTo('App\TariffFields');
    }
}
