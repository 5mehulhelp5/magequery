<?php

namespace Acme\Sorter\Model;

class Chain
{
    public function __construct(private array $sorted = [], private array $unsorted = [])
    {
    }
}
