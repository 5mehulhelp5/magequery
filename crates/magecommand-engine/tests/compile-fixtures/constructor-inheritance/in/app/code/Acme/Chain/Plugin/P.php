<?php

namespace Acme\Chain\Plugin;

class P
{
    public function afterRun($subject, string $result): string
    {
        return $result;
    }
}
