<?php

namespace Acme\Areas\Plugin;

class Graph
{
    public function afterRun($subject, $result)
    {
        return $result;
    }
}
