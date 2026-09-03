<?php

/** PSR-0: no namespace, underscores ARE the separator. */
class Acme_Legacy_Model_Thing
{
    public function __construct(private string $name = 'thing')
    {
    }
}
