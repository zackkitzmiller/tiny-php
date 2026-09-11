[![CI](https://github.com/zackkitzmiller/tiny-php/actions/workflows/ci.yml/badge.svg)](https://github.com/zackkitzmiller/tiny-php/actions/workflows/ci.yml)

# Tiny

Tiny is a small reversible ID obfuscator for PHP. It encodes integers into a custom alphabet and decodes them back again.

## What's modernized

- PHP 8.1+ baseline
- PSR-4 autoloading
- PHPUnit 11 test suite
- GitHub Actions CI
- Safer alphabet validation and decoding errors

## Installation

```bash
composer require zackkitzmiller/tiny
```

## Usage

```php
<?php

use ZackKitzmiller\Tiny;

$tiny = new Tiny('5SX0TEjkR1mLOw8Gvq2VyJxIFhgCAYidrclDWaM3so9bfzZpuUenKtP74QNH6B');

echo $tiny->to(5);
// E

echo $tiny->from('E');
// 5

echo $tiny->to(126);
// XX

echo $tiny->from('XX');
// 126
```

## Character set rules

Your alphabet must:

- contain at least 2 characters
- only contain unique characters
- stay fixed forever once you start issuing encoded IDs

Tiny throws an exception when the alphabet is invalid or when you try to decode characters that are not in the configured alphabet.

## Generate an alphabet

After installing dependencies, generate a fresh 62-character alphabet with:

```bash
./bin/genset
```

You can also generate one in code:

```php
$set = \ZackKitzmiller\Tiny::generateSet();
```

The legacy `Tiny::generate_set()` helper is still available for backwards compatibility.

This release does introduce a package-level modernization break: Composer autoloading now uses PSR-4 for `ZackKitzmiller\\` classes, so consumers relying on older PSR-0-era assumptions should verify their integration when upgrading.

The Laravel integration classes continue to autoload from the `ZackKitzmiller\\Laravel5\\` namespace under `src/ZackKitzmiller/Laravel5/`.

## Development

```bash
composer install
composer test
composer check
```

## Legacy Laravel integration

The repository still includes the original Laravel integration classes for older applications, but the package is now centered on the framework-agnostic core library.

For Laravel integration:

- register `ZackKitzmiller\TinyServiceProvider` or `ZackKitzmiller\Laravel5\TinyServiceProvider`
- optionally register the `ZackKitzmiller\Facades\Tiny` facade alias
- publish the config with `php artisan vendor:publish --tag=tiny-config`
- set `TINY_KEY` in your environment or run `php artisan tiny:generate`
