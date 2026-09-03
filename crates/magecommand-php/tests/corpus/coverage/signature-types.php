<?php
// coverage/signature-types.php — the full type-expression surface: nullable,
// unions, intersections, DNF, variadics, by-ref, static/never/true/false/
// mixed/self/parent returns, leading-backslash names, and defaults including
// heredoc, nowdoc, and `new` in initializers.

namespace Corp\Signatures;

use Corp\Modifiers\BaseIfc;
use Corp\Modifiers\IntBacked;

interface Counting
{
}

class SignatureTypes
{
    public function nullable(?string $a, ?IntBacked $b = null): ?BaseIfc
    {
        return null;
    }

    public function union(int|string $a, null|false $b, IntBacked|BaseIfc $c): int|false
    {
        return 0;
    }

    public function intersection(Counting&BaseIfc $both): Counting&BaseIfc
    {
        return $this;
    }

    public function dnf((Counting&BaseIfc)|null $maybe, (Counting&BaseIfc)|IntBacked $either): (Counting&BaseIfc)|BaseIfc
    {
        return $this;
    }

    public function variadic(string ...$all): void
    {
    }

    public function variadicTyped(?int ...$nums): void
    {
    }

    public function byRef(int &$out, ?bool &$flag = null): void
    {
    }

    public function mixedRef(&...$everything): void
    {
    }

    public function returnsStatic(): static
    {
        return $this;
    }

    public function returnsNever(): never
    {
        throw new \RuntimeException('never');
    }

    public function returnsTrue(): true
    {
        return true;
    }

    public function returnsFalse(): false
    {
        return false;
    }

    public function returnsMixed(): mixed
    {
        return null;
    }

    public function returnsSelf(): self
    {
        return $this;
    }

    public function returnsParent(): parent
    {
        return parent::instance();
    }

    public function absoluteTypes(\Corp\Modifiers\BaseIfc $x = null): \Corp\Modifiers\IntBacked
    {
        return \Corp\Modifiers\IntBacked::First;
    }

    public function heredocDefault(
        $text = <<<EOT
line one
line "two"
EOT,
        $num = 1,
    ): void {
    }

    public function nowdocDefault($raw = <<<'NOW'
    indented $notinterp
    NOW, $n = 2): void
    {
    }

    public function newInDefault($bag = new \ArrayObject([1, 2]), $scalar = PHP_INT_MAX): void
    {
    }

    public function callableAndArray(callable $cb, iterable $it, object $o, array $a): array|callable
    {
        return [];
    }
}
