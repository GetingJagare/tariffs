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
 * @property string $params
 * @property float $price_per_day
 * @property float $start_balance
 * @property string $unlimited
 */
class Tariffs extends Model
{
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
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function region()
    {
        return $this->belongsTo('App\Regions');
    }
}
