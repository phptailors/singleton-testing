<?php declare(strict_types=1);

namespace Tailors\Testing\Lib\Singleton\Constraint;

use PHPUnit\Framework\Constraint\Constraint;

/**
 * Constraint that accepts object/class that is cloneable.
 *
 * @api
 */
final class IsCloneable extends Constraint
{
    /**
     * @psalm-pure
     */
    #[\Override]
    public function toString(): string
    {
        return 'is cloneable';
    }

    /**
     * @param mixed $other
     *
     * @psalm-template T
     *
     * @psalm-param T $other
     *
     * @psalm-assert-if-true (T is string ? class-string|trait-string : object) $other
     */
    #[\Override]
    protected function matches(mixed $other): bool
    {
        if (!$this->checkArgumentType($other)) {
            return false;
        }

        $class = new \ReflectionClass($other);

        return $class->isCloneable();
    }

    /**
     * @param mixed $other
     *
     * @psalm-template T
     *
     * @psalm-param T $other
     *
     * @psalm-assert-if-true (T is string ? class-string|trait-string : object) $other
     */
    private function checkArgumentType(mixed $other): bool
    {
        return is_object($other) || is_string($other) && $this->checkStringArgument($other);
    }

    /**
     * @param string $other
     *
     * @psalm-assert-if-true class-string|trait-string $other
     */
    private function checkStringArgument(string $other): bool
    {
        return class_exists($other) || trait_exists($other);
    }
}
