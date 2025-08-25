<?php

namespace MichaelCrowcroft\BingWebmaster;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use MichaelCrowcroft\BingWebmaster\BingWebmaster;

class BingWebmasterServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('bing-webmaster-laravel')
            ->hasConfigFile();
    }

    public function packageBooted(): void
    {
        $this->publishes([
            __DIR__.'/../config/bing-webmaster.php' => config_path('bing-webmaster.php'),
        ], 'bing-webmaster-config');
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(BingWebmaster::class, function ($app) {
            return new BingWebmaster(); // No token - must be set explicitly
        });
    }
}
