<?php

namespace Acme\Init\Model;

class Paths
{
    public const DIR_KEY = 'acme/init/dir';

    public function __construct(
        private string $dir = 'fallback-default',
        private array $nested = []
    ) {
    }
}
