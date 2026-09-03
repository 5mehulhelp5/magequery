<?php

namespace Acme\Builtin\Plugin;

use Acme\Builtin\Model\Bag;

class Watch
{
    public function afterCount(Bag $subject, int $result): int
    {
        return $result;
    }
}
