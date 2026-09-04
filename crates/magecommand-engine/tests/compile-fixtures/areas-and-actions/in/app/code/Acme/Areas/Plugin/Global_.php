<?php

namespace Acme\Areas\Plugin;

class Global_
{
    public function afterRun($subject, $result)
    {
        return $result;
    }
}
