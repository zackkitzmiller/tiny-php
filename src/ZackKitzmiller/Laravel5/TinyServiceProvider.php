<?php

declare(strict_types=1);

namespace ZackKitzmiller\Laravel5;

use Illuminate\Support\ServiceProvider;
use RuntimeException;
use ZackKitzmiller\EnvironmentKeyUpdater;
use ZackKitzmiller\Tiny;

class TinyServiceProvider extends ServiceProvider
{
    private const CONFIG_PATH = __DIR__ . '/../../config/config.php';

    public function boot(): void
    {
        if (function_exists('config_path')) {
            $this->publishes([
                self::CONFIG_PATH => config_path('tiny.php'),
            ], 'tiny-config');
        }

        $this->app->singleton('tiny.generate', static function ($app) {
            return new TinyGenerateCommand($app['files']);
        });

        $this->commands(['tiny.generate']);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(self::CONFIG_PATH, 'tiny');

        $this->app->singleton('tiny', static function () {
            $key = config('tiny.key') ?: getenv(EnvironmentKeyUpdater::PRIMARY_KEY) ?: getenv(EnvironmentKeyUpdater::LEGACY_KEY);

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
