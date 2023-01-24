<?php

declare(strict_types=1);

namespace Tailors\Tests\Testing\Lib\Singletoni\Constraint;

use PHPUnit\Framework\TestCase;
use Tailors\Testing\Lib\Singleton\Constraint\IsCloneable;

final class CloneableClass52U81
{
}

final class NonCloneableClass52U81
{
    private function __clone()
    {
    }
}

/**
 * @author Paweł Tomulik <pawel@tomulik.pl>
 *
 * @covers \Tailors\Testing\Lib\Singleton\Constraint\IsCloneable
 *
 * @internal
 */
final class IsCloneableTest extends TestCase
{
    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    public function testToString(): void
    {
        $constraint = new IsCloneable();
        $this->assertSame('is cloneable', $constraint->toString());
    }

    /**
     * @return array<array>
     */
    public static function providerInvalidArgument(): array
    {
        return [
            [null],
            [123],
            ['$*%(&$'],
        ];
    }

    /**
     * @dataProvider providerInvalidArgument
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    public function testNotMatchesInvalidArgument(mixed $other): void
    {
        $this->assertThat($other, $this->logicalNot(new IsCloneable()));
    }

    /**
     * @return array<array>
     */
    public static function providerNonCloneableArgument(): array
    {
        return [
            [new NonCloneableClass52U81()],
            [NonCloneableClass52U81::class],
        ];
    }

    /**
     * @dataProvider providerNonCloneableArgument
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    public function testNotMatchesNonCloneableArgument(mixed $other): void
    {
        $this->assertThat($other, $this->logicalNot(new IsCloneable()));
    }

    /**
     * @return array<array>
     */
    public static function providerSingletonArgument(): array
    {
        return [
            [new CloneableClass52U81()],
            [CloneableClass52U81::class],
        ];
    }

    /**
     * @dataProvider providerSingletonArgument
     *
     * @psalm-suppress MissingThrowsDocblock
     */
    public function testMatchesSingletonArgument(mixed $other): void
    {
        $this->assertThat($other, new IsCloneable());
    }
}
