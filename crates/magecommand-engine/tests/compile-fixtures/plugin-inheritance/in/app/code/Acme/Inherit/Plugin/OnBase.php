<?php

namespace Acme\Inherit\Plugin;

class OnBase
{
    public function afterTarget($subject, $result)
    {
        return $result;
    }
}
