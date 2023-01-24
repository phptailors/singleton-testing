<?php

declare(strict_types=1);

namespace Tailors\Tests\Testing\Lib\Singleton\Constraint;

use PHPUnit\Framework\TestCase;
use Tailors\Testing\Lib\Singleton\Constraint\IsCloneable;

final class CloneableClass52U81 {}

final class NonCloneableClass52U81
{
    private function __clone() {}
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
     * @psalm-suppress MissingThrowsDocblock
     */
    public function testNotMatchesInvalidArgument(): void
    {
        $this->assertThat(null, $this->logicalNot(new IsCloneable()));
        $this->assertThat(123, $this->logicalNot(new IsCloneable()));
        $this->assertThat('$*%(&$', $this->logicalNot(new IsCloneable()));
    }

    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    public function testNotMatchesNonCloneableArgument(): void
    {
        $this->assertThat(new NonCloneableClass52U81(), $this->logicalNot(new IsCloneable()));
        $this->assertThat(NonCloneableClass52U81::class, $this->logicalNot(new IsCloneable()));
    }

    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    public function testMatchesSingletonArgument(): void
    {
        $this->assertThat(new CloneableClass52U81(), new IsCloneable());
        $this->assertThat(CloneableClass52U81::class, new IsCloneable());
    }
}
