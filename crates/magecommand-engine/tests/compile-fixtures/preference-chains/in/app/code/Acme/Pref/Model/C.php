<?php

namespace Acme\Pref\Model;

use Acme\Pref\Api\AInterface;
use Acme\Pref\Api\BInterface;
use Acme\Pref\Api\DInterface;

class C implements AInterface, BInterface, DInterface
{
    public function __construct(private string $tag = 'plain')
    {
    }

    public function run(): void
    {
    }
}
