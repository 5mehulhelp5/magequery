<?php

namespace Acme\Zerokey\Model;

class Holder
{
    public function __construct(private array $cfg = [], private array $other = [])
    {
    }
}
