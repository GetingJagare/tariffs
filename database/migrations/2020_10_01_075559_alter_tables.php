<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tariff_field_values', function (Blueprint $table) {
            $table->renameColumn('tariff_id', 'tariffs_id');
            $table->renameColumn('field_id', 'tariff_fields_id');
        });

        Schema::table('tariff_fields', function (Blueprint $table) {
            $table->renameColumn('type_id', 'tariff_field_types_id');
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
