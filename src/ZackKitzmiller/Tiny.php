<?php

declare(strict_types=1);

namespace ZackKitzmiller;

use InvalidArgumentException;

class Tiny
{
    private string $set;

    public function __construct(string $set)
    {
        self::assertValidSet($set);

        $this->set = $set;
    }

    public function to(int|string $id): string
    {
        $encoded = '';
        $value = $this->normalizeId($id);
        $radix = strlen($this->set);

        do {
            $remainder = $value % $radix;
            $encoded = $this->set[$remainder] . $encoded;
            $value = intdiv($value - $remainder, $radix);
        } while ($value > 0);

        return $encoded;
    }

    public function from(string $value): int
    {
        if ($value === '') {
            return 0;
        }

        $radix = strlen($this->set);
        $decoded = 0;

        foreach (str_split($value) as $character) {
            $position = strpos($this->set, $character);

            if ($position === false) {
                throw new InvalidArgumentException(sprintf('Character "%s" is not in the Tiny character set.', $character));
            }

            $decoded = ($decoded * $radix) + $position;
        }

        return $decoded;
    }

    public static function generateSet(): string
    {
        $characters = [
            ...range('A', 'Z'),
            ...range('a', 'z'),
            ...range('0', '9'),
        ];

        shuffle($characters);

        return implode('', $characters);
    }

    public static function generate_set(): string
    {
        return self::generateSet();
    }

    private static function assertValidSet(string $set): void
    {
        if (strlen($set) < 2) {
            throw new InvalidCharacterSet('Tiny requires at least two unique characters in the set.');
        }

        if (count(array_unique(str_split($set))) !== strlen($set)) {
            throw new InvalidCharacterSet('Tiny character sets must only contain unique characters.');
        }
    }

    private function normalizeId(int|string $id): int
    {
        if (is_int($id)) {
            if ($id === PHP_INT_MIN) {
                throw new InvalidArgumentException('Tiny cannot encode values smaller than PHP_INT_MIN + 1.');
            }

            return abs($id);
        }

        if (! preg_match('/^[+-]?\d+$/', $id)) {
            throw new InvalidArgumentException('Tiny can only encode whole-number integers.');
        }

        $isNegative = str_starts_with($id, '-');
        $normalized = ltrim($id, '+-');
        $normalized = ltrim($normalized, '0');
        $normalized = $normalized === '' ? '0' : $normalized;
        $limit = $isNegative ? ltrim((string) PHP_INT_MIN, '-') : (string) PHP_INT_MAX;

        if (
            strlen($normalized) > strlen($limit)
            || (
                strlen($normalized) === strlen($limit)
                && strcmp($normalized, $limit) > 0
            )
        ) {
            throw new InvalidArgumentException('Tiny can only encode integers within PHP\'s native integer range.');
        }

        if ($isNegative && $normalized === ltrim((string) PHP_INT_MIN, '-')) {
            throw new InvalidArgumentException('Tiny cannot encode values smaller than PHP_INT_MIN + 1.');
        }

        return (int) $normalized;
    }
}
