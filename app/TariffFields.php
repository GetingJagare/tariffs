<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TariffFields extends Model
{
    protected $fillable = ['name', 'type'];

    public $timestamps = false;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo('App\TariffFieldTypes');
    }
}
