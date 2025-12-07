<?php

namespace Djoudi\LaravelH5p;

use Djoudi\LaravelH5p\Commands\MigrationCommand;
use Djoudi\LaravelH5p\Commands\ResetCommand;
use Djoudi\LaravelH5p\Console\Commands\CleanupCommand;
use Djoudi\LaravelH5p\Console\Commands\InstallCommand;
use Djoudi\LaravelH5p\Console\Commands\PublishCommand;
use Djoudi\LaravelH5p\Console\Commands\StatusCommand;
use Djoudi\LaravelH5p\Helpers\H5pHelper;
use Illuminate\Support\ServiceProvider;

class LaravelH5pServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton('LaravelH5p', function ($app) {
            return new LaravelH5p($app);
        });

        $this->app->bind('H5pHelper', function () {
            return new H5pHelper();
        });

        $this->app->singleton('command.laravel-h5p.migration', function ($app) {
            return new MigrationCommand();
        });

        $this->app->singleton('command.laravel-h5p.reset', function ($app) {
            return new ResetCommand();
        });

        $this->commands([
            'command.laravel-h5p.migration',
            'command.laravel-h5p.reset',
            InstallCommand::class,
            PublishCommand::class,
            CleanupCommand::class,
            StatusCommand::class,
        ]);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->loadRoutesFrom(__DIR__.'/../../routes/laravel-h5p.php');

        // config
        $this->publishes([
            __DIR__.'/../../config/laravel-h5p.php' => config_path('laravel-h5p.php'),
        ], 'laravel-h5p-config');

        $this->mergeConfigFrom(
            __DIR__.'/../../config/laravel-h5p.php',
            'laravel-h5p'
        );

        // language files
        $this->publishes([
            __DIR__.'/../../lang/en/laravel-h5p.php' => lang_path('en/laravel-h5p.php'),
            __DIR__.'/../../lang/fr/laravel-h5p.php' => lang_path('fr/laravel-h5p.php'),
            __DIR__.'/../../lang/ar/laravel-h5p.php' => lang_path('ar/laravel-h5p.php'),
        ], 'laravel-h5p-lang');

        // views
        $this->loadViewsFrom(__DIR__.'/../../views/h5p', 'laravel-h5p');
        $this->publishes([
            __DIR__.'/../../views/h5p' => resource_path('views/vendor/laravel-h5p'),
        ], 'laravel-h5p-views');

        // migrations
        $this->loadMigrationsFrom(__DIR__.'/../../migrations');
        $this->publishes([
            __DIR__.'/../../migrations/' => database_path('migrations'),
        ], 'laravel-h5p-migrations');

        // h5p assets
        $this->publishes([
            __DIR__.'/../../assets'                     => public_path('assets/vendor/laravel-h5p'),
            base_path('vendor/h5p/h5p-core/fonts')      => public_path('assets/vendor/h5p/h5p-core/fonts'),
            base_path('vendor/h5p/h5p-core/images')     => public_path('assets/vendor/h5p/h5p-core/images'),
            base_path('vendor/h5p/h5p-core/js')         => public_path('assets/vendor/h5p/h5p-core/js'),
            base_path('vendor/h5p/h5p-core/styles')     => public_path('assets/vendor/h5p/h5p-core/styles'),
            base_path('vendor/h5p/h5p-editor/ckeditor') => public_path('assets/vendor/h5p/h5p-editor/ckeditor'),
            base_path('vendor/h5p/h5p-editor/images')   => public_path('assets/vendor/h5p/h5p-editor/images'),
            base_path('vendor/h5p/h5p-editor/language') => public_path('assets/vendor/h5p/h5p-editor/language'),
            base_path('vendor/h5p/h5p-editor/libs')     => public_path('assets/vendor/h5p/h5p-editor/libs'),
            base_path('vendor/h5p/h5p-editor/scripts')  => public_path('assets/vendor/h5p/h5p-editor/scripts'),
            base_path('vendor/h5p/h5p-editor/styles')   => public_path('assets/vendor/h5p/h5p-editor/styles'),
        ], 'laravel-h5p-assets');
    }
}
