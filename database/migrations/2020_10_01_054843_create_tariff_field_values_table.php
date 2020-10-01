<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTariffFieldValuesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tariff_field_values', function (Blueprint $table) {
            $table->id();
            $table->integer('field_id')->nullable(false);
            $table->integer('tariff_id')->nullable(false);
            $table->addColumn('string', 'value', ['length' => 255]);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tariff_field_values');
    }
}
