<?php

namespace Acme\Prefix\Plugin;

use Acme\Prefix\Model\Subject;

/**
 * Interception/Definition/Runtime.php takes a 5-char prefix with no word
 * boundary and no guard against an empty remainder, so these all become
 * plugin entries: `afterwards` targets a method named `wards`,
 * `aroundabout` targets `about`, and bare `after`/`before`/`around` collapse
 * onto the EMPTY method name with a combined bitmask.
 */
class P
{
    public function afterGetName(Subject $s, string $r): string
    {
        return $r;
    }

    public function after()
    {
    }

    public function before()
    {
    }

    public function around()
    {
    }

    public function afterwards()
    {
    }

    public function aroundabout()
    {
    }

    public function beforeX(Subject $s)
    {
    }
}
