<?php

namespace Acme\Tie\Plugin;

class Delta
{
    public function afterGo($subject, string $result): string
    {
        return $result;
    }
}
