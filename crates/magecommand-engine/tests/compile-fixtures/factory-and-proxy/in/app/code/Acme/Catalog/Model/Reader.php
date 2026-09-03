<?php

namespace Acme\Catalog\Model;

class Reader
{
    public function __construct(private string $source = 'file')
    {
    }

    public function read(int $limit): array
    {
        return [];
    }

    public function name(): string
    {
        return $this->source;
    }
}
