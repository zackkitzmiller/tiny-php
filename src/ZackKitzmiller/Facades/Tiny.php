<?php

declare(strict_types=1);

namespace ZackKitzmiller\Facades;

use Illuminate\Support\Facades\Facade;

class Tiny extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'tiny';
    }
}