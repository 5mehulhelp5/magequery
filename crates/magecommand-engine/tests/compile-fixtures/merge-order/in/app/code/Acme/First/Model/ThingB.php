<?php

namespace Acme\First\Model;

use Acme\First\Api\ThingInterface;

class ThingB implements ThingInterface
{
    public function __construct(private string $label = '', private string $kept = '')
    {
    }

    public function go(): void
    {
    }
}
