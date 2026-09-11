<?php

declare(strict_types=1);

namespace ZackKitzmiller\Laravel5;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use RuntimeException;
use ZackKitzmiller\EnvironmentKeyUpdater;
use ZackKitzmiller\Tiny;

class TinyGenerateCommand extends Command
{
    protected $signature = 'tiny:generate {--env=}';

    protected $description = 'Generate a valid key';

    public function __construct(private readonly Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $key = Tiny::generateSet();
        $path = $this->environmentFilePath();

        $content = is_file($path) ? $this->readEnvironmentFile($path) : '';
        $content = EnvironmentKeyUpdater::updateContents($content, $key);

        $this->writeEnvironmentFile($path, $content);
        $this->laravel['config']->set('tiny.key', $key);
        $this->laravel['config']->set('zackkitzmiller/tiny::key', $key);

        $this->info("Tiny key [{$key}] has been set.");

        return self::SUCCESS;
    }

    public function fire(): int
    {
        return $this->handle();
    }

    private function readEnvironmentFile(string $path): string
    {
        $content = $this->files->get($path);

        if (! is_string($content)) {
            throw new RuntimeException(sprintf('Unable to read environment file at %s.', $path));
        }

        return $content;
    }

    private function writeEnvironmentFile(string $path, string $content): void
    {
        if ($this->files->put($path, $content) === false) {
            throw new RuntimeException(sprintf('Unable to write environment file at %s.', $path));
        }
    }

    private function environmentFilePath(): string
    {
        if (method_exists($this->laravel, 'environmentFilePath')) {
            return $this->laravel->environmentFilePath();
        }

        if (method_exists($this->laravel, 'basePath')) {
            return $this->laravel->basePath('.env');
        }

        return $this->laravel['path.base'] . '/.env';
    }
}
