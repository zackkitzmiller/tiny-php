<?php

declare(strict_types=1);

namespace ZackKitzmiller;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

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
        $environment = $this->environmentOption();

        if ($environment !== null) {
            if (method_exists($this->laravel, 'environmentPath')) {
                return rtrim($this->laravel->environmentPath(), '/\\') . '/.env.' . $environment;
            }

            if (method_exists($this->laravel, 'basePath')) {
                return $this->laravel->basePath('.env.' . $environment);
            }

            return $this->laravel['path.base'] . '/.env.' . $environment;
        }

        if (method_exists($this->laravel, 'environmentFilePath')) {
            return $this->laravel->environmentFilePath();
        }

        if (method_exists($this->laravel, 'basePath')) {
            return $this->laravel->basePath('.env');
        }

        return $this->laravel['path.base'] . '/.env';
    }

    private function environmentOption(): ?string
    {
        $environment = $this->option('env');

        if (! is_string($environment) || $environment === '') {
            return null;
        }

        if (preg_match('/\A[A-Za-z0-9_-]+\z/', $environment) !== 1) {
            throw new \InvalidArgumentException('The --env option may only contain letters, numbers, dashes, and underscores.');
        }

        return $environment;
    }

    private function updateLegacyConfigFile(string $key): void
    {
        $path = $this->legacyConfigFilePath();

        if ($path === null || ! is_file($path)) {
            return;
        }

        $contents = $this->readEnvironmentFile($path);
        $escapedKey = addcslashes($key, "\\'");
        $updated = preg_replace_callback(
            "/^([ \\t]*'key'\\s*=>\\s*)([^,]+)(,?[ \\t]*$)/m",
            static fn (array $matches): string => $matches[1] . "'" . $escapedKey . "'" . $matches[3],
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

        $environment = $this->environmentOption();
        $environment = $environment !== null ? $environment . '/' : '';

        return $this->laravel['path'] . "/config/packages/zackkitzmiller/tiny/{$environment}config.php";
    }
}
