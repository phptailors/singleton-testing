<?php

declare(strict_types=1);

namespace Tailors\Testing\Lib\Singleton;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\LogicalNot;
use PHPUnit\Framework\ExpectationFailedException;
use Tailors\Lib\Singleton\SingletonException;
use Tailors\PHPUnit\HasMethodTrait;
use Tailors\PHPUnit\InvalidArgumentException;
use Tailors\Testing\Lib\Singleton\Constraint\IsCloneable;

/**
 * @author Paweł Tomulik <pawel@tomulik.pl>
 *
 * @api
 */
trait AssertIsSingletonTrait
{
    use HasMethodTrait;

    abstract public static function assertIsCallable($actual, string $message = ''): void;

    abstract public static function assertIsObject($actual, string $message = ''): void;

    abstract public static function assertInstanceOf(string $expected, $actual, string $message = ''): void;

    abstract public static function assertTrue($condition, string $message = ''): void;

    abstract public static function assertSame($expected, $actual, string $message = ''): void;

    abstract public static function assertEquals($expected, $actual, string $message = ''): void;

    abstract public static function assertThat($value, Constraint $constraint, string $message = ''): void;

    abstract public static function logicalNot(Constraint $constraint): LogicalNot;

    abstract public static function fail(string $message): void;

    /**
     * @psalm-assert class-string $class
     *
     * @throws \PHPUnit\Framework\Exception
     * @throws ExpectationFailedException
     * @throws InvalidArgumentException
     */
    public static function assertIsSingleton(string $class, string $getInstance = 'getInstance'): void
    {
        // palm bug #9151
        self::assertIsClass($class);

        self::assertHasMethod('private function __construct', $class);
        self::assertHasMethod(sprintf('public static function %s', $getInstance), $class);

        $function = [$class, $getInstance];
        self::assertIsCallable($function);

        /** @var mixed */
        $instance = call_user_func($function);
        $message = sprintf('Failed asserting that %s::%s() returns an instance of %s', $class, $getInstance, $class);
        self::assertInstanceOf($class, $instance, $message);

        self::assertIsIdempotent($function, sprintf('%s::%s', $class, $getInstance));

        self::assertNotCloneable($class);

        self::assertIsObject($instance);
        self::assertThrowsSingletonExceptionOnUnserialize($instance);
    }

    /**
     * @throws ExpectationFailedException
     *
     * @psalm-assert class-string $string
     */
    public static function assertIsClass(string $string, string $message = ''): void
    {
        if ('' === $message) {
            $message = sprintf('Failed asserting that %s is a class', $string);
        }
        self::assertTrue(class_exists($string), $message);
    }

    /**
     * @param callable():mixed $function
     *
     * @throws ExpectationFailedException
     */
    public static function assertIsIdempotent(callable $function, string $name): void
    {
        /** @var mixed */
        $val1 = call_user_func($function);

        /** @var mixed */
        $val2 = call_user_func($function);
        $message = sprintf('Failed asserting that %s() is idempotent', $name);
        self::assertSame($val1, $val2, $message);
    }

    /**
     * @throws ExpectationFailedException
     */
    public static function assertNotCloneable(mixed $actual, string $message = ''): void
    {
        static::assertThat($actual, self::logicalNot(self::isCloneable()), $message);
    }

    /**
     * @throws AssertionFailedError
     * @throws ExpectationFailedException
     */
    public static function assertThrowsSingletonExceptionOnUnserialize(object $object): void
    {
        $str = serialize($object);

        $class = get_class($object);

        try {
            unserialize($str);
        } catch (SingletonException $exception) {
            $expect = sprintf('Cannot unserialize singleton %s', $class);
            $message = sprintf("Failed asserting that exception message is '%s'", $expect);
            self::assertEquals($expect, $exception->getMessage(), $message);

            return;
        } catch (\Exception) {
        }
        self::fail(sprintf('Failed asserting that exception of type "%s" is thrown', SingletonException::class));
    }

    public static function isCloneable(): IsCloneable
    {
        return new IsCloneable();
    }
}

// vim: syntax=php sw=4 ts=4 et:
