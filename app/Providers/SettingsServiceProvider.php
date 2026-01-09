<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\SystemSetting;

class SettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Helper global
        if (!function_exists('setting')) {
            function setting(string $key, $default = null)
            {
                return SystemSetting::get($key, $default);
            }
        }

        // Compartilhar configs com todas as views
        View::composer('*', function ($view) {
            $view->with([
                'appName'       => SystemSetting::get('app_name', 'FinanceTracker'),
                'logoPath'      => SystemSetting::get('logo_path'),
                'faviconPath'   => SystemSetting::get('favicon_path'),
                'primaryColor'  => SystemSetting::get('primary_color', '#4F46E5'),
            ]);
        });
    }
}
