<?php

namespace Acme\Iface\Model;

class Base
{
    public function __construct(protected string $label = '', protected array $items = [])
    {
    }
}
