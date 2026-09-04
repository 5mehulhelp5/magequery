<?php

namespace Acme\Magic\Model;

/**
 * The exact interceptor skip list. INTERCEPTED (surprising): __call, __get,
 * __set, __isset, __unset, __toString, __invoke, __debugInfo, __serialize,
 * __unserialize. SKIPPED: __construct, __destruct, __sleep, __wakeup,
 * __clone, _resetState, plus anything final or static.
 *
 * __sleep/__wakeup are deliberately UNTYPED: the Interceptor trait declares
 * them untyped, and a typed override in the subject makes the generated class
 * fatal on load in real Magento too.
 */
class Wizard
{
    public function __call($name, $args)
    {
        return null;
    }

    public function __get($name)
    {
        return null;
    }

    public function __set($name, $value)
    {
    }

    public function __isset($name)
    {
        return false;
    }

    public function __unset($name)
    {
    }

    public function __toString(): string
    {
        return 'wizard';
    }

    public function __invoke(int $n): int
    {
        return $n;
    }

    public function __debugInfo(): array
    {
        return [];
    }

    public function __sleep()
    {
        return [];
    }

    public function __wakeup()
    {
    }

    public function __clone()
    {
    }

    public function _resetState(): void
    {
    }

    public function ordinary(): int
    {
        return 1;
    }
}
