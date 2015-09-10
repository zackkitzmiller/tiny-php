<?php namespace League\Tiny;

use Illuminate\Support\ServiceProvider;
use Illuminate\Foundation\Application;

class TinyServiceProvider extends ServiceProvider
{

    public function boot()
    {
        $this->app['tiny.generate'] = $this->app->share(function ($app) {
            return new TinyGenerateCommand($app['files']);
        });

        $this->commands('tiny.generate');
    }

    public function register()
    {
        $this->app['tiny'] = $this->app->share(function ($app) {

            //Parse Laravel Version
            $pattern = '/(?P<major>\d+)\.(?P<minor>\d+)(?:\.(?P<hotfix>\d+))/';

            if (!preg_match($pattern, Application::VERSION, $version)) {
                $this->warning('Could not detect a version of Laravel. Pre-Laravel 5 behaviour assumed. Writing Key to config file.');
            }

            if (is_array($version) && $version['major'] >= '5') {
                $key = getenv('LEAGUE_TINY_KEY');
            } else {
                $key = $app['config']['league/tiny::key'];
            }

            return new Tiny($key);
        });
    }

    public function provides()
    {
        return array('tiny');
    }

}
