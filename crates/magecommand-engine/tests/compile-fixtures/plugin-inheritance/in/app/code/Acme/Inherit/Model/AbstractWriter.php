<?php

namespace Acme\Inherit\Model;

use Acme\Inherit\Api\WriterInterface;

abstract class AbstractWriter implements WriterInterface
{
    public function write(string $payload): int
    {
        return 0;
    }

    abstract public function target(): string;
}
