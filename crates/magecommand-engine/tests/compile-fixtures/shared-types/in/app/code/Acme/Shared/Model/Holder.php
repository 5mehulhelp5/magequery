<?php

namespace Acme\Shared\Model;

class Holder
{
    public function __construct(private PerRequest $fresh, private Singleton $one)
    {
    }
}
