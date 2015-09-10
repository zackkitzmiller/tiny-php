<?php namespace League\Tiny;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;

class TinyGenerateCommand extends Command
{

    protected $name = 'tiny:generate';

    protected $description = "Generate a valid key";

    public function __construct(Filesystem $files)
    {
        parent::__construct();

        $this->files = $files;
    }

    public function fire()
    {
        $key = Tiny::generate_set();
        
        //Parse Laravel Version
        $pattern = '/(?P<major>\d+)\.(?P<minor>\d+)(?:\.(?P<hotfix>\d+))/';

        if (!preg_match($pattern, Application::VERSION, $version)) {
            $this->warning('Could not detect a version of Laravel. Pre-Laravel 5 behaviour assumed. Writing Key to config file.');
        }

        if (is_array($version) && $version['major'] >= '5') {
            //Laravel 5 integrates vlucas' dotenv, so store key in .ENV file.
            $path = base_path('.env');

            if (file_exists($path) && getenv('LEAGUE_TINY_KEY') !== false) {
                //Already set, so replace it.
                file_put_contents($path, str_replace(
                    'LEAGUE_TINY_KEY='.getenv('LEAGUE_TINY_KEY'), 'LEAGUE_TINY_KEY='.$key, file_get_contents($path)
                ));
            } else {
                //Append it to the bottom
                $fp = fopen($path, 'a');
                fwrite($fp, "\nLEAGUE_TINY_KEY=$key\n");
                fclose($fp);
            }

        } else {
            //If Version 4, or default, store file in config file, as per before.
            list($path, $contents) = $this->getKeyFile();

            $contents = str_replace($this->laravel['config']['league/tiny::key'], $key, $contents);
            
            $this->files->put($path, $contents);

            $this->laravel['config']['league/tiny::key'] = $key;

        } 

        $this->info("Tiny key [$key] has been set.");
    }

    protected function getKeyFile()
    {
        $env = $this->option('env') ? $this->option('env') . '/' : '';

        $contents = $this->files->get($path = $this->laravel['path'] . "/config/packages/league/tiny/{$env}config.php");

        return array($path, $contents);
    }

}
