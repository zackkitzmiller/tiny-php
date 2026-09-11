<?php

declare(strict_types=1);

namespace ZackKitzmiller;

use Illuminate\Support\ServiceProvider;

class TinyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->package('zackkitzmiller/tiny', 'zackkitzmiller/tiny', __DIR__ . '/../');

        $this->app['tiny.generate'] = $this->app->share(function ($app) {
            return new TinyGenerateCommand($app['files']);
        });

        $this->commands('tiny.generate');
    }

    public function register(): void
    {
        $this->app['tiny'] = $this->app->share(function ($app) {
            return new Tiny((string) $app['config']['zackkitzmiller/tiny::key']);
        });
    }

    public function provides(): array
    {
        return ['tiny'];
    }
}