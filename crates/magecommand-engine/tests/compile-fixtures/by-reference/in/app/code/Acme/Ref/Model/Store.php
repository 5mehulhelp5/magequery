<?php

namespace Acme\Ref\Model;

class Store
{
    private array $data = ['k' => 1];

    /** A by-ref RETURN. Magento emits `public function & get(...)` — note the
     *  space after the ampersand — and a ternary body, which silently destroys
     *  the reference. */
    public function &get(string $k)
    {
        return $this->data[$k];
    }

    /** By-ref parameters, including one with a default. */
    public function fill(array &$out, ?int &$n = null): bool
    {
        return true;
    }

    /** A by-ref VARIADIC: the signature keeps the &, the forwarding call
     *  drops it. */
    public function each(&...$rows): void
    {
    }
}
