<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use ZackKitzmiller\EnvironmentKeyUpdater;
use ZackKitzmiller\InvalidCharacterSet;
use ZackKitzmiller\Tiny;

final class TinyTest extends TestCase
{
    private Tiny $tiny;

    protected function setUp(): void
    {
        $this->tiny = new Tiny('5SX0TEjkR1mLOw8Gvq2VyJxIFhgCAYidrclDWaM3so9bfzZpuUenKtP74QNH6B');
    }

    public function testToTiny(): void
    {
        self::assertSame('E', $this->tiny->to(5));
    }

    public function testFromTiny(): void
    {
        self::assertSame(5, $this->tiny->from('E'));
    }

    public function testReversingRandomInt(): void
    {
        for ($i = 0; $i <= 1000; $i++) {
            self::assertSame($i, $this->tiny->from($this->tiny->to($i)));
        }
    }

    public function testNegativeValuesAreNormalized(): void
    {
        self::assertSame($this->tiny->to(25), $this->tiny->to(-25));
    }

    public function testNumericStringsAreSupported(): void
    {
        self::assertSame($this->tiny->to(25), $this->tiny->to('0025'));
    }

    public function testGenerateRandomSetsWork(): void
    {
        for ($i = 0; $i <= 100; $i++) {
            $tiny = new Tiny(Tiny::generateSet());
            self::assertSame($i, $tiny->from($tiny->to($i)));
        }
    }

    public function testGenerateSetAliasRemainsAvailable(): void
    {
        self::assertSame(62, strlen(Tiny::generate_set()));
    }

    public function testGenerateSetIsUnique(): void
    {
        $set = Tiny::generateSet();
        self::assertCount(62, array_unique(str_split($set)));
    }

    public function testConstructorRejectsDuplicateCharacters(): void
    {
        $this->expectException(InvalidCharacterSet::class);

        new Tiny('aa');
    }

    public function testConstructorRejectsSmallCharacterSets(): void
    {
        $this->expectException(InvalidCharacterSet::class);

        new Tiny('a');
    }

    public function testFromRejectsUnknownCharacters(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->tiny->from('!');
    }

    public function testToRejectsNonIntegerStrings(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->tiny->to('12abc');
    }

    public function testToRejectsOutOfRangeNumericStrings(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->tiny->to((string) PHP_INT_MAX . '0');
    }

    public function testToRejectsMinimumIntegerString(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->tiny->to((string) PHP_INT_MIN);
    }

    public function testEnvironmentKeyUpdaterReplacesExistingKeys(): void
    {
        $contents = "APP_ENV=testing\nLEAGUE_TINY_KEY=old-key\n";

        self::assertSame(
            "APP_ENV=testing\nTINY_KEY=new-key\n",
            EnvironmentKeyUpdater::updateContents($contents, 'new-key')
        );
    }

    public function testEnvironmentKeyUpdaterAppendsMissingKeys(): void
    {
        $contents = "APP_ENV=testing\n";

        self::assertSame(
            "APP_ENV=testing\nTINY_KEY=new-key\n",
            EnvironmentKeyUpdater::updateContents($contents, 'new-key')
        );
    }
}
