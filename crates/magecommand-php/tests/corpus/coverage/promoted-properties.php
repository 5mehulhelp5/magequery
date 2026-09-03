<?php
// coverage/promoted-properties.php — constructor promotion in every shape:
// all visibilities, readonly-promoted, bare readonly (implicit public),
// defaults (incl. string/array with commas, class const), nullable and union
// types, asymmetric visibility (8.4, consumed), and mixed promoted + plain
// params. Promoted visibility/readonly flags are pinned by the fingerprint.

namespace Corp\Promoted;

use Corp\Modifiers\BaseIfc;

class Promoted
{
    public function __construct(
        private readonly string $name,
        protected int $count = 0,
        public readonly ?\Corp\Modifiers\IntBacked $status = null,
        public string $label = 'none',
        readonly float $ratio = 1.0,
        private array $tags = ['a', 'b'],
        private string $greeting = "hello, 'world'",
        protected \Corp\Constants\ConstExpr $expr = new \Corp\Constants\ConstExpr(),
        private int|string $either = 'either',
    ) {
    }

    public function describe(): string
    {
        return $this->name;
    }
}

class PromotedHooksReady
{
    public function __construct(
        public readonly BaseIfc $ifc,
    ) {
    }
}

class PlainCtor
{
    public function __construct()
    {
    }
}
