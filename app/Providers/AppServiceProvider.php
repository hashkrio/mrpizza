<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
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
        View::share('languages', collect([
            (object) [
                'locale' => 'en',
                'name' => 'English',
            ],
            (object) [
                'locale' => 'pt',
                'name' => 'Português',
            ],
        ]));
    }
}
