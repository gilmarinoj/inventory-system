<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use App\Services\BcvRateService;
use App\Models\DolarParalelo;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        require_once app_path('Helpers/PriceHelper.php');
        Schema::defaultStringLength(191);
        Paginator::useBootstrap();

        // Settings carga...
        if (! $this->app->runningInConsole()) {
            $settings = Setting::all('key', 'value')->keyBy('key')->transform(fn($s) => $s->value)->toArray();
            config(['settings' => $settings]);
            config(['app.name' => $settings['app_name'] ?? 'Laravel']);
        }

        // UNA SOLA fuente de verdad para $dolar_bcv
        View::composer('*', function ($view) {
            $view->with('dolar_bcv', app(\App\Services\BcvRateService::class)->getRate());
        });

        View::composer('*', function ($view) {
            $view->with('dolar_paralelo', app(\App\Models\DolarParalelo::class)->tasaActualRaw());
        });
    }
}
