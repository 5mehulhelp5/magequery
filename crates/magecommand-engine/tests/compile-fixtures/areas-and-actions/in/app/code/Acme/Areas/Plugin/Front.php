<?php

namespace Acme\Areas\Plugin;

class Front
{
    public function afterRun($subject, $result)
    {
        return $result;
    }
}
