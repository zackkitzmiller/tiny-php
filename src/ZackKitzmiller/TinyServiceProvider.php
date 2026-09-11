<?php

declare(strict_types=1);

namespace ZackKitzmiller;

use Illuminate\Support\ServiceProvider;
use RuntimeException;

class TinyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole() && function_exists('config_path')) {
            $this->publishes([
                __DIR__ . '/../config/config.php' => config_path('tiny.php'),
            ], 'tiny-config');

            $this->commands(['tiny.generate']);
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/config.php', 'tiny');

        $this->app->singleton('tiny.generate', function ($app) {
            return new TinyGenerateCommand($app['files']);
        });

        $this->app->singleton('tiny', function ($app) {
            $key = $app['config']->get('tiny.key');

            if ($key === null || $key === false) {
                $primaryKey = getenv(EnvironmentKeyUpdater::PRIMARY_KEY);
                $legacyKey = getenv(EnvironmentKeyUpdater::LEGACY_KEY);
                $key = $primaryKey !== false ? $primaryKey : $legacyKey;
            }

            if (! is_string($key) || $key === '') {
                throw new RuntimeException('A Tiny character set must be configured before resolving the Tiny service.');
            }

            return new Tiny($key);
        });
    }

    public function provides(): array
    {
        return ['tiny'];
    }
}