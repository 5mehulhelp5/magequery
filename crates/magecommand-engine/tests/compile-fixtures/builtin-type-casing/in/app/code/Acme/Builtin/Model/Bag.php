<?php

namespace Acme\Builtin\Model;

/**
 * Built-ins spelled in three casings. PINS: they produce NO interception
 * entries at all — `relations_of` drops relations with no scanned record, so
 * a built-in never reaches the map from a source `implements` or `extends`.
 *
 * This is NOT a regression test for the duplicate-key bug (#100, canonicalize
 * built-in type names). That one needed a vendor shape this fixture does not
 * reproduce: emptying BUILTIN_TYPES leaves every golden here unchanged.
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
