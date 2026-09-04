<?php

namespace Acme\Neverr\Plugin;

use Acme\Neverr\Model\Hard;

class P
{
    public function afterBoom(Hard $subject, $result)
    {
        return $result;
    }
}
