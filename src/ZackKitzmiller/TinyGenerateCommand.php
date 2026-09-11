<?php

declare(strict_types=1);

namespace ZackKitzmiller;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class TinyGenerateCommand extends Command
{
    protected $name = 'tiny:generate';

    protected $description = 'Generate a valid key';

    public function __construct(private readonly Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        [$path, $contents] = $this->getKeyFile();
        $key = Tiny::generateSet();
        $currentKey = (string) $this->laravel['config']['zackkitzmiller/tiny::key'];

        $contents = str_replace($currentKey, $key, $contents);

        $this->files->put($path, $contents);
        $this->laravel['config']['zackkitzmiller/tiny::key'] = $key;

        $this->info("Tiny key [{$key}] has been set.");

        return self::SUCCESS;
    }

    public function fire(): int
    {
        return $this->handle();
    }

    protected function getKeyFile(): array
    {
        $env = $this->option('env') ? $this->option('env') . '/' : '';
        $path = $this->laravel['path'] . "/config/packages/zackkitzmiller/tiny/{$env}config.php";

        return [$path, $this->files->get($path)];
    }
}
