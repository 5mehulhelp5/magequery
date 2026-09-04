<?php

namespace Acme\Slash\Model;

use Acme\Slash\Api\PortInterface;

class Port implements PortInterface
{
    public function __construct(private ?Dep $dep = null)
    {
    }

    public function open(): bool
    {
        return true;
    }
}
