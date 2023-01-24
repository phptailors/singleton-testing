<?php

declare(strict_types=1);

namespace Tailors\Tests\Testing\Lib\Singleton;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Tailors\Lib\Singleton\SingletonException;
use Tailors\Testing\Lib\Singleton\AssertIsSingletonTrait;

// Below are four components that comprise a singleton

// 1/4: Private constructor.
trait PrivateConstructorTrait
{
    private function __construct()
    {
    }
}

// 2/4: Private constructor.
trait PrivateCloneTrait
{
    private function __clone()
    {
    }
}

// 3/4: Public __wakeup() method that always throws SingletonException
trait PublicWakeupThrowingSingletonExceptionTrait
{
    /**
     * @throws \Tailors\Lib\Singleton\SingletonException
     */
    public function __wakeup()
    {
        throw new SingletonException(sprintf('Cannot unserialize singleton %s', self::class));
    }
}

// 4/4: Public static getInstance() method that is idempotent.
trait PublicStaticGetInstanceTrait
{
    /**
     * @psalm-var ?self
     */
    private static ?object $instance;

    /** @psalm-suppress PossiblyUnusedMethod */
    public static function getInstance(): self
    {
        if (!isset(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}

final class ClassWithMissingConstructor
{
    use PrivateCloneTrait;
    use PublicStaticGetInstanceTrait;
    use PublicWakeupThrowingSingletonExceptionTrait;
}

final class ClassWithPublicConstructor
{
    use PrivateCloneTrait;
    use PublicStaticGetInstanceTrait;
    use PublicWakeupThrowingSingletonExceptionTrait;

    public function __construct()
    {
    }
}

final class ClassWithMissingClone
{
    use PrivateConstructorTrait;
    use PublicStaticGetInstanceTrait;
    use PublicWakeupThrowingSingletonExceptionTrait;
}

final class ClassWithPublicClone
{
    use PrivateConstructorTrait;
    use PublicStaticGetInstanceTrait;
    use PublicWakeupThrowingSingletonExceptionTrait;

    public function __clone()
    {
    }
}

final class ClassWithMissingWakeup
{
    use PrivateConstructorTrait;
    use PrivateCloneTrait;
    use PublicStaticGetInstanceTrait;
}

final class ClassWithNonThrowingWakeup
{
    use PrivateConstructorTrait;
    use PrivateCloneTrait;
    use PublicStaticGetInstanceTrait;

    public function __wakeup()
    {
    }
}

final class ClassWithMissingGetInstance
{
    use PrivateConstructorTrait;
    use PrivateCloneTrait;
    use PublicWakeupThrowingSingletonExceptionTrait;
}

final class ClassWithNonStaticGetInstance
{
    use PrivateConstructorTrait;
    use PrivateCloneTrait;
    use PublicWakeupThrowingSingletonExceptionTrait;

    /** @psalm-suppress PossiblyUnusedMethod */
    public function getInstance(): self
    {
        return $this;
    }
}

final class ClassWithNonIdempotentGetInstance
{
    use PrivateConstructorTrait;
    use PrivateCloneTrait;
    use PublicWakeupThrowingSingletonExceptionTrait;

    /** @psalm-suppress PossiblyUnusedMethod */
    public static function getInstance(): self
    {
        return new self();
    }
}

final class ClassThrowingRuntimeExceptionFromWakeup
{
    use PrivateConstructorTrait;
    use PrivateCloneTrait;
    use PublicStaticGetInstanceTrait;

    /**
     * @throws \RuntimeException
     */
    public function __wakeup()
    {
        throw new \RuntimeException('foo');
    }
}

final class ClassWithGetInstanceReturningString
{
    use PrivateConstructorTrait;
    use PrivateCloneTrait;
    use PublicWakeupThrowingSingletonExceptionTrait;

    /** @psalm-suppress PossiblyUnusedMethod */
    public static function getInstance(): string
    {
        return '';
    }
}

final class ClassThatIsASingleton
{
    use PrivateConstructorTrait;
    use PrivateCloneTrait;
    use PublicWakeupThrowingSingletonExceptionTrait;
    use PublicStaticGetInstanceTrait;
}

/**
 * @author Paweł Tomulik <pawel@tomulik.pl>
 *
 * @covers \Tailors\Testing\Lib\Singleton\AssertIsSingletonTrait
 *
 * @internal
 */
final class AssertIsSingletonTraitTest extends TestCase
{
    use AssertIsSingletonTrait;

    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    public function testClassWithMissingConstructor(): void
    {
        $class = ClassWithMissingConstructor::class;
        $this->expectException(ExpectationFailedException::class);
        $message = sprintf("Failed asserting that '%s' has private method __construct()", $class);
        $this->expectExceptionMessage($message);
        $this->assertIsSingleton($class);
    }

    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    public function testClassWithPublicConstructor(): void
    {
        $class = ClassWithPublicConstructor::class;
        $this->expectException(ExpectationFailedException::class);
        $message = sprintf("Failed asserting that '%s' has private method __construct", $class);
        $this->expectExceptionMessage($message);
        $this->assertIsSingleton($class);
    }

    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    public function testClassWithMissingClone(): void
    {
        $class = ClassWithMissingClone::class;
        $this->expectException(ExpectationFailedException::class);
        $message = sprintf("Failed asserting that '%s' is not cloneable", $class);
        $this->expectExceptionMessage($message);
        $this->assertIsSingleton($class);
    }

    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    public function testClassWithPublicClone(): void
    {
        $class = ClassWithPublicClone::class;
        $this->expectException(ExpectationFailedException::class);
        $message = sprintf("Failed asserting that '%s' is not cloneable", $class);
        $this->expectExceptionMessage($message);
        $this->assertIsSingleton($class);
    }

    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    public function testClassWithMissingWakeup(): void
    {
        $class = ClassWithMissingWakeup::class;
        $exception = SingletonException::class;
        $this->expectException(AssertionFailedError::class);
        $message = sprintf('Failed asserting that exception of type "%s" is thrown', $exception);
        $this->expectExceptionMessage($message);
        $this->assertIsSingleton($class);
    }

    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    public function testClassWithNonThrowingWakeup(): void
    {
        $class = ClassWithNonThrowingWakeup::class;
        $exception = SingletonException::class;
        $this->expectException(AssertionFailedError::class);
        $message = sprintf('Failed asserting that exception of type "%s" is thrown', $exception);
        $this->expectExceptionMessage($message);
        $this->assertIsSingleton($class);
    }

    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    public function testClassWithMissingGetInstance(): void
    {
        $class = ClassWithMissingGetInstance::class;
        $this->expectException(ExpectationFailedException::class);
        $message = sprintf("Failed asserting that '%s' has public static method getInstance().", $class);
        $this->expectExceptionMessage($message);
        $this->assertIsSingleton($class);
    }

    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    public function testClassWithNonStaticGetInstance(): void
    {
        $class = ClassWithNonStaticGetInstance::class;
        $this->expectException(ExpectationFailedException::class);
        $message = sprintf("Failed asserting that '%s' has public static method getInstance().", $class);
        $this->expectExceptionMessage($message);
        $this->assertIsSingleton($class);
    }

    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    public function testClassWithNonIdempotentGetInstance(): void
    {
        $class = ClassWithNonIdempotentGetInstance::class;
        $message = sprintf('Failed asserting that %s::getInstance() is idempotent', $class);
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($message);
        $this->assertIsSingleton($class);
    }

    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    public function testClassThrowingRuntimeExceptionFromWakeup(): void
    {
        $class = ClassThrowingRuntimeExceptionFromWakeup::class;
        $message = sprintf('Failed asserting that exception of type "%s" is thrown', SingletonException::class);
        $this->expectException(AssertionFailedError::class);
        $this->expectExceptionMessage($message);
        $this->assertIsSingleton($class);
    }

    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    public function testClassWithGetInstanceReturningString(): void
    {
        $class = ClassWithGetInstanceReturningString::class;
        $message = sprintf('Failed asserting that %s::getInstance() returns an instance of %s', $class, $class);
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage($message);
        $this->assertIsSingleton($class);
    }

    /**
     * @psalm-suppress MissingThrowsDocblock
     */
    public function testClassThatIsASingleton(): void
    {
        $class = ClassThatIsASingleton::class;
        $this->assertIsSingleton($class);
    }
}
