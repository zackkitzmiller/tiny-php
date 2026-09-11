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
        $primaryPattern = '/^' . self::PRIMARY_KEY . '=.*$/m';
        $legacyPattern = '/^' . self::LEGACY_KEY . '=.*$/m';

        if (preg_match($primaryPattern, $contents) === 1) {
            $updated = preg_replace_callback(
                $primaryPattern,
                static fn (): string => self::PRIMARY_KEY . '=' . $key,
                $contents,
                1
            );

            if ($updated !== null) {
                return $updated;
            }
        }

        if (preg_match($legacyPattern, $contents) === 1) {
            $updated = preg_replace_callback(
                $legacyPattern,
                static fn (): string => self::PRIMARY_KEY . '=' . $key,
                $contents,
                1
            );

            if ($updated !== null) {
                return $updated;
            }
        }

        if ($trimmed === '') {
            return self::PRIMARY_KEY . '=' . $key . PHP_EOL;
        }

        return $trimmed . PHP_EOL . self::PRIMARY_KEY . '=' . $key . PHP_EOL;
    }
}
