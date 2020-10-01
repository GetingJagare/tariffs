<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MovingData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tariffs = \App\Tariffs::all();

        foreach ($tariffs as $tariff) {

            foreach ($tariff::PARAMS_LABELS as $fieldAlias => $fieldName) {

                $isUnLimited = in_array($fieldAlias, ['whatsapp', 'viber', 'skype', 'network']);
                $isParams = in_array($fieldAlias, ['sms', 'gb', 'min']);

                if (!($tariffField = \App\TariffFields::where(['name' => $fieldName])->first())) {

                    $typeAlias = !$isUnLimited ? 'text' : 'checkbox';
                    /** @var \App\TariffFieldTypes $fieldType */
                    $fieldType = \App\TariffFieldTypes::where(['alias' => $typeAlias])->first();

                    $tariffField = new \App\TariffFields(['name' => $fieldName, 'type_id' => $fieldType->id]);
                    $tariffField->save();

                }

                $tariffParams = json_decode($tariff->params, true);
                $tariffUnlimited = json_decode($tariff->unlimited, true);

                $tariffFieldValue = new \App\TariffFieldValues([
                    'tariff_id' => $tariff->id,
                    'field_id' => $tariffField->id,
                    'value' => $isParams ? $tariffParams[$fieldAlias] : ($isUnLimited ? $tariffUnlimited[$fieldAlias]
                        : $tariff->{$fieldAlias})
                ]);

                $tariffFieldValue->save();

            }

        }

        Schema::table('tariffs', function (Blueprint $table) {

            $table->dropColumn('params');
            $table->dropColumn('price_per_day');
            $table->dropColumn('start_balance');
            $table->dropColumn('unlimited');

        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
