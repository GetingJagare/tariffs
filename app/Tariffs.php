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
 * @property string $params
 * @property float $price_per_day
 * @property float $price
 * @property float $start_balance
 * @property string $unlimited
 */
class Tariffs extends Model
{
    protected $fillable = ['name', 'description', 'image_link', 'params', 'price_per_day', 'price', 'start_balance', 'unlimited'];

    public const PARAMS_LABELS = [
        'sms' => 'СМС',
        'gb' => 'Гб',
        'min' => 'Минуты',
        'start_balance' => 'Стартовый баланс',
        'price_per_day' => 'Абонентская плата в сутки',
        'whatsapp' => 'Безлимит на WhatsApp',
        'viber' => 'Безлимит на Viber',
        'skype' => 'Безлимит на Skype',
        'network' => 'Безлимит внутри сети',
        'region' => 'Регион'
    ];

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
}
