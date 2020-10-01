<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AppendFieldTypes extends Migration
{
    public const TYPES = [
        'text' => 'Текстовый',
        'checkbox' => 'Флажок',
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tariff_field_types', function (Blueprint $table) {
            $table->addColumn('string', 'alias', ['length' => 255]);
        });

        foreach (self::TYPES as $alias => $name) {

            $newFieldType = new \App\TariffFieldTypes(['alias' => $alias, 'name' => $name]);
            $newFieldType->save();

        }
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
