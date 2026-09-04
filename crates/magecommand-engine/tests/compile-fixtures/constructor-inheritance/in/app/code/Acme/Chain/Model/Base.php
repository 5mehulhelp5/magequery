<?php

namespace Acme\Chain\Model;

class Base
{
    public function __construct(protected string $label = 'x', protected int $n = 1)
    {
    }

    public function run(): string
    {
        return $this->label;
    }
}
