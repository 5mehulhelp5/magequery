<?php

namespace Acme\Vmerge\Model;

class Logger
{
    public function __construct(private array $handlers = [])
    {
    }
}
