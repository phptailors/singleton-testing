[![PHPUnit](https://github.com/phptailors/singleton-testing/actions/workflows/phpunit.yml/badge.svg)](https://github.com/phptailors/singleton-testing/actions/workflows/phpunit.yml)
[![Composer Require Checker](https://github.com/phptailors/singleton-testing/actions/workflows/composer-require-checker.yml/badge.svg)](https://github.com/phptailors/singleton-testing/actions/workflows/composer-require-checker.yml)
[![BC Check](https://github.com/phptailors/singleton-testing/actions/workflows/backward-compatibility-check.yml/badge.svg)](https://github.com/phptailors/singleton-testing/actions/workflows/backward-compatibility-check.yml)
[![Psalm](https://github.com/phptailors/singleton-testing/actions/workflows/psalm.yml/badge.svg)](https://github.com/phptailors/singleton-testing/actions/workflows/psalm.yml)
[![PHP CS Fixer](https://github.com/phptailors/singleton-testing/actions/workflows/php-cs-fixer.yml/badge.svg)](https://github.com/phptailors/singleton-testing/actions/workflows/php-cs-fixer.yml)

phptailors/singleton-testing
============================

PHPUnit extension for testing implementations of [phptailors/singleton-interface][singleton-interface].

## Installation

```bash
composer require --dev "phptailors/singleton-testing:^1.0"
composer require --dev "phpunit/phpunit"
```


## Usage


```php
<?php

use PHPUnit\Framework\TestCase;
use Tailors\Testing\Lib\Singleton\AssertIsSingletonTrait;

final class MySingletonTest extends TestCase
{
    use AssertIsSingletonTrait;

    public function testMySingletonIsSingleton(): void
    {
        $this->assertIsSingleton(MySingleton::class);
    }
}

```

## How a class is tested

The following tests are performed by ``assertIsSingleton($class)``:

1. Assert that the the provided string ``$class`` is a class.
2. Assert that ``$class`` has private constructor.
3. Assert taht ``$class`` has public static method ``getInstance()``.
4. Assert that ``$class::getInstance()`` is callable.
5. Assert that ``$class::getInstance()`` returns an instance of ``$class``.
6. Assert that ``$class::getInstance()`` is idempotent.
7. Assert that ``$class`` is not cloneable.
8. Assert taht it throws [Tailors\Lib\Singleton\SingletonException][SingletonException] on [unserialize()][unserialize].

The name of the ``getInstance()`` method may be customized, for example:
```php
    $this->assertIsSingleton(MySingleton::class, getInstance: "getSingleInstance")
```
will use ``getSingleInstance`` instead of ``getInstance``.




[singleton-interface]: <https://packagist.org/packages/phptailors/singleton-interface>
[SingletonException]: <https://github.com/phptailors/singleton-interface/blob/master/src/SingletonException.php>
[unserialize]: <https://www.php.net/manual/en/function.unserialize.php>
