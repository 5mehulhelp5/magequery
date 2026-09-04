<?php

namespace Acme\Slash\Plugin;

class P
{
    public function afterOpen($subject, bool $result): bool
    {
        return $result;
    }
}
