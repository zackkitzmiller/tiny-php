<?php namespace ZackKitzmiller\Tiny;

use Illuminate\Support\ServiceProvider;

class TinyServiceProvider extends ServiceProvider {

    public function boot()
    {
        $this->package('zackkitzmiller/tiny', 'zackkitzmiller/tiny');
    }

    public function register()
    {
        $this->app['tiny'] = $this->app->share(function($app)
        {
            $key = $app['config']['zackkitzmiller/tiny::key'];

            return new Tiny($key);
        });
    }

    public function provides()
    {
        return array('tiny');
    }

}