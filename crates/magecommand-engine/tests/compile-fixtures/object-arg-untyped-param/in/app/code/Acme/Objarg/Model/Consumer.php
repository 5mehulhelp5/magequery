<?php

namespace Acme\Objarg\Model;

class Consumer
{
    public function __construct(
        private Dep $typed,
        private $untypedParam = null,
        private string $stringParam = '',
        private array $rest = []
    ) {
    }
}
