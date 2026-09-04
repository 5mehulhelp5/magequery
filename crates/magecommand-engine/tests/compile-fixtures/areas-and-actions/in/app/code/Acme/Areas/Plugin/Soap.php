<?php

namespace Acme\Areas\Plugin;

class Soap
{
    public function afterRun($subject, $result)
    {
        return $result;
    }
}
