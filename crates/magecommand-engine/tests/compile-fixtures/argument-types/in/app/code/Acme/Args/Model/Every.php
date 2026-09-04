<?php

namespace Acme\Args\Model;

class Every
{
    public const LIMIT = 7;
    public const DIR = 'var/acme';

    public function __construct(
        private string $text = '',
        private int $whole = 0,
        private float $fraction = 0.0,
        private bool $yes = false,
        private bool $no = true,
        private int $konst = 0,
        private $param = null,
        private ?Dep $obj = null,
        private ?Dep $unshared = null,
        private array $nested = [],
        private array $ordered = [],
        private array $untyped = []
    ) {
    }
}
