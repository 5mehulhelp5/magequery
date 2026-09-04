<?php

namespace Acme\Areas\Plugin;

class Rest
{
    public function afterRun($subject, $result)
    {
        return $result;
    }
}
