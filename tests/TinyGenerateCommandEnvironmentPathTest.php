<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

if (! class_exists(\Illuminate\Console\Command::class)) {
    eval(<<<'PHP'
namespace Illuminate\Console;

class Command
{
    public $laravel;

    private array $options = [];

    public function __construct()
    {
    }

    public function option($key)
    {
        return $this->options[$key] ?? null;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }
}
PHP);
}

if (! class_exists(\Illuminate\Filesystem\Filesystem::class)) {
    eval(<<<'PHP'
namespace Illuminate\Filesystem;

class Filesystem
{
}
PHP);
}

require_once __DIR__ . '/../src/ZackKitzmiller/TinyGenerateCommand.php';
require_once __DIR__ . '/../src/ZackKitzmiller/Laravel5/TinyGenerateCommand.php';

final class TinyGenerateCommandEnvironmentPathTest extends TestCase
{
    /**
     * @return array<string, array{class-string, string}>
     */
    public static function commandClasses(): array
    {
        return [
            'current command' => [\ZackKitzmiller\TinyGenerateCommand::class, '/var/www/current/.env'],
            'laravel5 command' => [\ZackKitzmiller\Laravel5\TinyGenerateCommand::class, '/var/www/legacy/.env'],
        ];
    }

    /**
     * @dataProvider commandClasses
     */
    public function testUsesDefaultEnvironmentFilePathWhenEnvOptionIsMissing(string $commandClass, string $defaultPath): void
    {
        $command = $this->makeCommand($commandClass, new FakeLaravelApplication(dirname($defaultPath), $defaultPath));

        self::assertSame($defaultPath, $this->environmentFilePath($command));
    }

    /**
     * @dataProvider commandClasses
     */
    public function testUsesEnvironmentSpecificFileWhenEnvOptionIsProvided(string $commandClass, string $defaultPath): void
    {
        $command = $this->makeCommand($commandClass, new FakeLaravelApplication(dirname($defaultPath), $defaultPath), 'staging');

        self::assertSame(dirname($defaultPath) . '/.env.staging', $this->environmentFilePath($command));
    }

    /**
     * @param class-string $commandClass
     */
    private function makeCommand(string $commandClass, FakeLaravelApplication $laravel, ?string $environment = null): object
    {
        $command = new $commandClass(new \Illuminate\Filesystem\Filesystem());
        $command->laravel = $laravel;
        $command->setOptions(['env' => $environment]);

        return $command;
    }

    private function environmentFilePath(object $command): string
    {
        $method = new ReflectionMethod($command, 'environmentFilePath');
        $method->setAccessible(true);

        /** @var string */
        return $method->invoke($command);
    }
}

final class FakeLaravelApplication implements ArrayAccess
{
    public function __construct(
        private string $basePath,
        private string $environmentFilePath,
    ) {
    }

    public function environmentFilePath(): string
    {
        return $this->environmentFilePath;
    }

    public function environmentPath(): string
    {
        return $this->basePath;
    }

    public function basePath(string $path = ''): string
    {
        return $path === '' ? $this->basePath : $this->basePath . '/' . ltrim($path, '/');
    }

    public function offsetExists(mixed $offset): bool
    {
        return $offset === 'path.base';
    }

    public function offsetGet(mixed $offset): mixed
    {
        return $offset === 'path.base' ? $this->basePath : null;
    }

    public function offsetSet(mixed $offset, mixed $value): void
    {
    }

    public function offsetUnset(mixed $offset): void
    {
    }
}
