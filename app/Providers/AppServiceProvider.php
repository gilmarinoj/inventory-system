<?php

namespace App\Providers;

use App\Models\Setting;
use App\Providers\AdminComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;

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

        if (! $this->app->runningInConsole()) {
            // 'key' => 'value'
            $settings = Setting::all('key', 'value')
                ->keyBy('key')
                ->transform(fn($setting) => $setting->value)
                ->toArray();
            config([
                'settings' => $settings
            ]);

            config(['app.name' => config('settings.app_name')]);
        }

        View::composer(['layouts.admin', 'layouts.partials.navbar'], AdminComposer::class);

        Paginator::useBootstrap();
    }
}
