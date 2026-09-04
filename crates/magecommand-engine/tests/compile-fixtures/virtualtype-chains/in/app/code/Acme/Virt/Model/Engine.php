<?php

namespace Acme\Virt\Model;

class Engine
{
    public function __construct(private string $mode = 'off', private int $depth = 0)
    {
    }
}
