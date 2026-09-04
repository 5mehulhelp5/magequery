<?php

namespace Acme\Dis\Plugin;

class Kept
{
    public function afterAct($subject, int $result): int
    {
        return $result;
    }
}
