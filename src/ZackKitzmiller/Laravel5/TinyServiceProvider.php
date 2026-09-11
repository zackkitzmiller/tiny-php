<?php

declare(strict_types=1);

namespace ZackKitzmiller\Laravel5;

use Illuminate\Support\ServiceProvider;
use RuntimeException;
use ZackKitzmiller\EnvironmentKeyUpdater;
use ZackKitzmiller\Tiny;

class TinyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (function_exists('config_path')) {
            $this->publishes([
                self::configPath() => config_path('tiny.php'),
            ], 'tiny-config');
        }

        $this->app->singleton('tiny.generate', static function ($app) {
            return new TinyGenerateCommand($app['files']);
        });

        $this->commands(['tiny.generate']);
    }

    public function register(): void
    {
        $this->mergeConfigFrom(self::configPath(), 'tiny');

        $this->app->singleton('tiny', static function ($app) {
            $key = $app['config']->get('tiny.key');

            if ($key === null || $key === false) {
                $key = getenv(EnvironmentKeyUpdater::PRIMARY_KEY);
            }

            if ($key === false) {
                $key = getenv(EnvironmentKeyUpdater::LEGACY_KEY);
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

    private static function configPath(): string
    {
        return dirname(__DIR__, 2) . '/config/config.php';
    }
}
