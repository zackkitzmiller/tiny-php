<?php

declare(strict_types=1);

namespace ZackKitzmiller\Laravel5;

use Illuminate\Console\Command;
use RuntimeException;
use ZackKitzmiller\Tiny;

class TinyGenerateCommand extends Command
{
    protected $signature = 'tiny:generate';

    protected $description = 'Generate a valid key';

    public function handle(): int
    {
        $key = Tiny::generateSet();
        $path = base_path('.env');
        $currentKey = getenv('TINY_KEY') ?: getenv('LEAGUE_TINY_KEY') ?: null;

        if (is_file($path)) {
            $originalContent = file_get_contents($path);

            if ($originalContent === false) {
                throw new RuntimeException(sprintf('Unable to read environment file at %s.', $path));
            }

            if ($currentKey !== null) {
                $content = str_replace(
                    ["TINY_KEY={$currentKey}", "LEAGUE_TINY_KEY={$currentKey}"],
                    "TINY_KEY={$key}",
                    $originalContent
                );
            } else {
                $content = rtrim($originalContent) . PHP_EOL . "TINY_KEY={$key}" . PHP_EOL;
            }
        } else {
            $content = "TINY_KEY={$key}" . PHP_EOL;
        }

        if (file_put_contents($path, $content) === false) {
            throw new RuntimeException(sprintf('Unable to write environment file at %s.', $path));
        }

        $this->info("Tiny key [{$key}] has been set.");

        return self::SUCCESS;
    }

    public function fire(): int
    {
        return $this->handle();
    }
}
