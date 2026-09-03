<?php
// coverage/anonymous-classes.php — anonymous classes (with ctor args,
// inheritance, trait use, and interface implementations) in method bodies
// and at top level. Anonymous classes have no FQCN and are skipped, not
// modeled — the gate pins: zero issues, and the NAMED neighbors survive
// intact around them.

namespace Corp\Anonymous;

use Corp\Modifiers\BaseIfc;

class AnonFactory
{
    public function handler(BaseIfc $base): object
    {
        return new class($base) extends \Corp\Modifiers\AbstractBase implements \Corp\Modifiers\ExtraIfc {
            use \Corp\Traits\Solo;

            public function __construct(private BaseIfc $base)
            {
                parent::__construct();
            }

            public function abstractMethod(string $x): bool
            {
                return strlen($x) > 0;
            }
        };
    }
}

$topLevel = new class {
    public function ping(): string
    {
        return 'pong';
    }
};

class AfterAnon
{
    public function fine(): bool
    {
        $handler = new class {
            public function handle(): void
            {
            }
        };

        return true;
    }
}
