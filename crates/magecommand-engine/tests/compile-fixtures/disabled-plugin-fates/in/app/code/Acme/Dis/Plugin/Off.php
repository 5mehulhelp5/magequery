<?php

namespace Acme\Dis\Plugin;

class Off
{
    public function afterAct($subject, int $result): int
    {
        return $result;
    }
}
