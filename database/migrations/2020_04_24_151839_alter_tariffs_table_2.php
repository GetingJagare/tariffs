<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTariffsTable2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tariffs', function (Blueprint $table) {

            $table->addColumn('text', 'description')->nullable(true)->after('name');
            $table->addColumn('text', 'image_link')->nullable(true)->after('description');
            $table->addColumn('integer', 'category_id')->nullable(false)->after('image_link');
            $table->addColumn('float', 'price')->nullable(false)->after('params');

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

            $table->dropColumn('description');
            $table->dropColumn('image_link');
            $table->dropColumn('category_id');
            $table->dropColumn('price');

        });
    }
}
