<?php

namespace Acme\Builtin\Model;

/**
 * Built-ins spelled in three casings.
 *
 * PHP class names are case-insensitive, so source may write a built-in any way
 * it likes; the real compiler reflects the type and gets the engine's own
 * spelling back. Keying on the use-site spelling instead leaks `arrayaccess`
 * and `ITERATOR` into the plugin-list files as keys of their own — which is
 * what this pins, in `*plugin-list.php` rather than in `interception.php`.
 *
 * The built-ins produce no `interception.php` entries at all (relations_of
 * drops relations with no scanned record), which is why an earlier version of
 * this header claimed the fixture did not cover the canonicalisation bug at
 * all. It does — just in a different file, and only visible against an install
 * where the framework supplies the ancestors.
 */
class Bag implements \arrayaccess, \Countable, \ITERATOR
{
    public function offsetExists($offset): bool
    {
        return false;
    }

    public function offsetGet($offset): mixed
    {
        return null;
    }

    public function offsetSet($offset, $value): void
    {
    }

    public function offsetUnset($offset): void
    {
    }

    public function count(): int
    {
        return 0;
    }

    public function current(): mixed
    {
        return null;
    }

    public function next(): void
    {
    }

    public function key(): mixed
    {
        return null;
    }

    public function valid(): bool
    {
        return false;
    }

    public function rewind(): void
    {
    }
}
