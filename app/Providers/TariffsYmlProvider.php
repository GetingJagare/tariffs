<?php

namespace App\Providers;

use App\Services\TariffsExporter;
use App\Services\TariffsImporter;
use App\Services\TariffsTemplate;
use Illuminate\Support\ServiceProvider;

class TariffsYmlProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(TariffsExporter::class, function ($app) {
            return new TariffsExporter();
        });

        $this->app->singleton(TariffsImporter::class, function ($app) {
            return new TariffsImporter(request());
        });

        $this->app->singleton(TariffsTemplate::class, function ($app) {
            return new TariffsTemplate();
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
