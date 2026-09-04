<?php

namespace Acme\Inherit\Plugin;

class OnIface
{
    public function afterWrite($subject, $result)
    {
        return $result;
    }
}
