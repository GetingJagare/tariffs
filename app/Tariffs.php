<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * Class Tariffs
 * @package App
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $image_link
 * @property Regions $region
 * @property Categories $category
 * @property int $region_id
 * @property int $category_id
 * @property float $price
 * @property TariffFieldValues[] $fieldValues
 */
class Tariffs extends Model
{
    protected $fillable = ['name', 'description', 'image_link', 'price'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function region()
    {
        return $this->belongsTo('App\Regions');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo('App\Categories');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function fieldValues()
    {
        return $this->hasMany('App\TariffFieldValues');
    }

    /**
     * @param mixed $field
     */
    public function addFieldValue($field)
    {

    }
}
