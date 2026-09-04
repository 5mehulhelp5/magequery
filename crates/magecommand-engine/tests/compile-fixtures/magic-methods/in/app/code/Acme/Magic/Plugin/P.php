<?php

namespace Acme\Magic\Plugin;

class P
{
    public function afterOrdinary($s, $r)
    {
        return $r;
    }

    public function afterToString($s, $r)
    {
        return $r;
    }

    public function after__call($s, $r)
    {
        return $r;
    }

    public function afterClone($s, $r)
    {
        return $r;
    }

    public function afterSleep($s, $r)
    {
        return $r;
    }
}
