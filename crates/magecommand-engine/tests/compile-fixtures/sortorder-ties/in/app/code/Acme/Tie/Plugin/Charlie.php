<?php

namespace Acme\Tie\Plugin;

class Charlie
{
    public function afterGo($subject, string $result): string
    {
        return $result;
    }
}
