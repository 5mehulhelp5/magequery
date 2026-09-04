<?php
namespace Acme\Order\Plugin;
use Acme\Order\Model\Typed;
class P
{
    public function afterInter(Typed $s, $r) { return $r; }
    public function afterUni(Typed $s, $r) { return $r; }
    public function afterDefs(Typed $s, $r) { return $r; }
}
