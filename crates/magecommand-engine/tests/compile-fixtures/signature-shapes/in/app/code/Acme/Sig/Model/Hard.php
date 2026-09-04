<?php

namespace Acme\Sig\Model;

class Hard
{
    public const FALLBACK = 'fb';

    /** Const-expression default, a union, and a trailing variadic (which may
     *  NOT be promoted — PHP rejects a variadic promoted property). */
    public function __construct(
        private string $named = self::FALLBACK,
        private array $opts = ['a' => 1],
        private ?int $maybe = null,
        private int|string $union = 0,
        mixed ...$rest
    ) {
    }

    /** Return types and parameter shapes the interceptor must reproduce. */
    public function collect(int &$counter, string ...$names): static
    {
        return $this;
    }

    public function maybe(?self $other = null): ?static
    {
        return null;
    }

    public function nothing(): void
    {
    }

    public function never_(): never
    {
        throw new \RuntimeException('x');
    }
}
