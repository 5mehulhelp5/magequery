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
 *
 * What this fixture CANNOT show, because its golden is computed with no
 * framework present: against a real install, `Traversable` ends up in a
 * different POSITION in the `_inherited` section of
 * frontend|global|primary|plugin-list.php. Magento appends it as a sibling in
 * the subject's own parent list (class_implements is transitive and flat); we
 * reach it later by another route. That section is consumed only by
 * isset/array_key_exists/indexed lookup (PluginList.php:174,191), so the
 * position has no runtime effect — but `di verify` is byte-exact, so a store
 * containing a miscased built-in would show a diff there. A clean 302-module
 * install is 16/16 identical, so nothing in the reference install reaches it.
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
