<?php

namespace Acme\Inherit\Api;

interface WriterInterface
{
    public function write(string $payload): int;
}
