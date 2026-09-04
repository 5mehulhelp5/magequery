<?php

namespace Acme\Tie\Plugin;

class Bravo
{
    public function afterGo($subject, string $result): string
    {
        return $result;
    }
}
