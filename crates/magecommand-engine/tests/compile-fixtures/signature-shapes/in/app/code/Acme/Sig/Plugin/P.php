<?php

namespace Acme\Sig\Plugin;

use Acme\Sig\Model\Hard;

class P
{
    public function afterCollect(Hard $subject, $result)
    {
        return $result;
    }

    public function beforeMaybe(Hard $subject, ?Hard $other = null): array
    {
        return [$other];
    }

    public function aroundNothing(Hard $subject, callable $proceed): void
    {
        $proceed();
    }
}
