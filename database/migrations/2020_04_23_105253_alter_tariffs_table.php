<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTariffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tariffs', function (Blueprint $table) {
            $table->addColumn('text', 'params')->nullable(true)->after('region_id');
            $table->addColumn('float', 'price_per_day')->nullable(true)->after('params');
            $table->addColumn('float', 'start_balance')->nullable(true)->after('price_per_day');
            $table->addColumn('text', 'unlimited', ['length' => 255])->nullable(true)->after('start_balance');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tariffs', function (Blueprint $table) {
            $table->dropColumn('params');
            $table->dropColumn('price_per_day');
            $table->dropColumn('start_balance');
            $table->dropColumn('unlimited');
        });
    }
}
