<?php

declare(strict_types=1);

namespace ZackKitzmiller;

final class EnvironmentKeyUpdater
{
    public const PRIMARY_KEY = 'TINY_KEY';

    public const LEGACY_KEY = 'LEAGUE_TINY_KEY';

    public static function updateContents(string $contents, string $key): string
    {
        $trimmed = rtrim($contents);
        $patterns = [
            '/^' . self::PRIMARY_KEY . '=.*$/m',
            '/^' . self::LEGACY_KEY . '=.*$/m',
        ];

        foreach ($patterns as $pattern) {
            $updated = preg_replace_callback(
                $pattern,
                static fn (): string => self::PRIMARY_KEY . '=' . $key,
                $contents,
                1,
                $count
            );

            if ($updated !== null && $count > 0) {
                return $updated;
            }
        }

        if ($trimmed === '') {
            return self::PRIMARY_KEY . '=' . $key . PHP_EOL;
        }

        return $trimmed . PHP_EOL . self::PRIMARY_KEY . '=' . $key . PHP_EOL;
    }
}
