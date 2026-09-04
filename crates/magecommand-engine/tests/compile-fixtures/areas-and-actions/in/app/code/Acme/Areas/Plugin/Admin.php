<?php

namespace Acme\Areas\Plugin;

class Admin
{
    public function afterRun($subject, $result)
    {
        return $result;
    }
}
