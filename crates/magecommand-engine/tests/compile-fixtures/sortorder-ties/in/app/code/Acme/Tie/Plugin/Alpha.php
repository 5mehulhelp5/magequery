<?php

namespace Acme\Tie\Plugin;

class Alpha
{
    public function afterGo($subject, string $result): string
    {
        return $result;
    }
}
