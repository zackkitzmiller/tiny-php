<?php

declare(strict_types=1);

namespace ZackKitzmiller\Laravel5;

use Illuminate\Support\ServiceProvider;
use RuntimeException;
use ZackKitzmiller\Tiny;

class TinyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->app->singleton('tiny.generate', static function () {
            return new TinyGenerateCommand();
        });

        $this->commands('tiny.generate');
    }

    public function register(): void
    {
        $this->app->singleton('tiny', static function () {
            $key = getenv('TINY_KEY') ?: getenv('LEAGUE_TINY_KEY');

            if (! is_string($key) || $key === '') {
                throw new RuntimeException('A Tiny character set must be configured via TINY_KEY or LEAGUE_TINY_KEY.');
            }

            return new Tiny($key);
        });
    }

    public function provides(): array
    {
        return ['tiny'];
    }
}
