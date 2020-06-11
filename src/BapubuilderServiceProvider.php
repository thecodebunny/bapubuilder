<?php

namespace Thecodebunny\Bapubuilder;

use Illuminate\Support\ServiceProvider;
use Thecodebunny\Bapubuilder\Commands\CreateTheme;

class BapubuilderServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        /*
         * Optional methods to load your package assets
         */
        // $this->loadTranslationsFrom(__DIR__.'/../resources/lang', 'bapubuilder');
        // $this->loadViewsFrom(__DIR__.'/../resources/views', 'bapubuilder');
        // $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        // $this->loadRoutesFrom(__DIR__.'/routes.php');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/bapubuilder.php' => config_path('bapubuilder.php'),
            ], 'config');

            // Publishing the views.
            /*$this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/bapubuilder'),
            ], 'views');*/

            if ($this->app->runningInConsole()) {
                $this->commands([
                    CreateTheme::class,
                ]);
            }

            // Publishing assets.
            /*$this->publishes([
                __DIR__.'/../resources/assets' => public_path('vendor/bapubuilder'),
            ], 'assets');*/

            // Publishing the translation files.
            /*$this->publishes([
                __DIR__.'/../resources/lang' => resource_path('lang/vendor/bapubuilder'),
            ], 'lang');*/

            // Registering package commands.
            $this->publishes([
                __DIR__ . '/../themes/bapu' => base_path(config('bapubuilder.theme.folder_url') . '/bapu'),
            ], 'bapu-theme');


            // $this->commands([]);
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        // Automatically apply the package configuration
        $this->mergeConfigFrom(__DIR__.'/../config/bapubuilder.php', 'bapubuilder');

        // Register the main class to use with the facade
        $this->app->singleton('bapubuilder', function () {
            return new Bapubuilder;
        });
    }
}
