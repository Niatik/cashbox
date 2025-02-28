<?php

namespace App\Providers;

use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class FilamentAssetsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Регистрируем наш JavaScript в Filament
        FilamentAsset::register([
            // Используем Vite для получения правильного URL к скомпилированному ресурсу
            Js::make('app', Vite::asset('resources/js/app.js')),
        ]);
    }
}
