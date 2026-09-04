<?php

namespace Acme\Gen\Model;

class Heavy
{
    public function __construct(private string $cfg = '')
    {
    }

    public function work(int $n): array
    {
        return [];
    }
}
