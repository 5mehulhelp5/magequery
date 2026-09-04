<?php

namespace Acme\Areas\Plugin;

class Cron
{
    public function afterRun($subject, $result)
    {
        return $result;
    }
}
