<?php
// coverage/class-exists-guard.php — the conditional-declaration shapes the
// engine's scan contract depends on: braced `if (class_exists(…)) { … } else
// { … }` shims (first branch wins, conditional = true) and the alternative-
// syntax `if (…): … else: … endif;` guard (TIG PostNL shape — sits at brace
// depth 0, so it is scanned as a normal top-level declaration). Companion to
// the lib.rs unit tests; here the manifest pins the outcomes.

namespace Corp\Guards;

use Vendor\Missing\Dependency;

if (class_exists(\Vendor\Missing\Dependency::class)) {
    class Shim extends Dependency
    {
        public function afterIsVirtual($subject, $result)
        {
            return $result;
        }
    }
} else {
    class Shim
    {
    }
}

if (!class_exists(Dependency\Factory::class)) :
    class AltSyntaxShim
    {
        public function __construct(\Magento\Framework\ObjectManagerInterface $om, Dependency $dep)
        {
        }
    }
else :
    class AltSyntaxShim
    {
        public function __construct(\Magento\Framework\ObjectManagerInterface $om)
        {
        }
    }
endif;

class GuardNeighbor
{
    public function alive(): bool
    {
        return true;
    }
}
