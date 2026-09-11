<?php

declare(strict_types=1);

namespace ZackKitzmiller;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class TinyGenerateCommand extends Command
{
    protected $signature = 'tiny:generate';

    protected $description = 'Generate a valid key';

    public function __construct(private readonly Filesystem $files)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $key = Tiny::generateSet();
        $environmentPath = $this->environmentFilePath();
        $contents = is_file($environmentPath) ? $this->readEnvironmentFile($environmentPath) : '';
        $contents = EnvironmentKeyUpdater::updateContents($contents, $key);

        $this->writeEnvironmentFile($environmentPath, $contents);
        $this->updateLegacyConfigFile($key);
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
            throw new \RuntimeException(sprintf('Unable to read environment file at %s.', $path));
        }

        return $content;
    }

    private function writeEnvironmentFile(string $path, string $content): void
    {
        if ($this->files->put($path, $content) === false) {
            throw new \RuntimeException(sprintf('Unable to write environment file at %s.', $path));
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

    private function updateLegacyConfigFile(string $key): void
    {
        $path = $this->legacyConfigFilePath();

        if ($path === null || ! is_file($path)) {
            return;
        }

        $contents = $this->readEnvironmentFile($path);
        $updated = preg_replace_callback(
            "/('key'\\s*=>\\s*)([^,]+)(,?)/",
            static fn (array $matches): string => $matches[1] . "'" . $key . "'" . $matches[3],
            $contents,
            1,
            $count
        );

        if ($updated !== null && $count > 0) {
            $this->writeEnvironmentFile($path, $updated);
        }
    }

    private function legacyConfigFilePath(): ?string
    {
        if (! isset($this->laravel['path'])) {
            return null;
        }

        $environment = $this->option('env');
        $environment = is_string($environment) && $environment !== '' ? $environment . '/' : '';

        return $this->laravel['path'] . "/config/packages/zackkitzmiller/tiny/{$environment}config.php";
    }
}
