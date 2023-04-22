<?php

namespace Iankumu\Mpesa;

use Iankumu\Mpesa\Console\InstallMpesaPackage;
use Illuminate\Support\ServiceProvider;

class MpesaServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind('iankumu-mpesa', function ($app) {
            return new Mpesa;
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

        if ($this->app->runningInConsole()) {

            $this->publishes([
                __DIR__.'/../config/mpesa.php' => config_path('mpesa.php'),
            ], 'mpesa-config');

            $this->commands([
                InstallMpesaPackage::class,
            ]);
        }
    }
}
