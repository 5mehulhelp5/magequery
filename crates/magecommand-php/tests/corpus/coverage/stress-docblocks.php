<?php

namespace Corp\Stress\Docs;

/**
 * Magento's real texture: doc comments that quote code, annotations with
 * braces, and lookalike declarations in every comment form. None of it may
 * reach the parser's output.
 *
 *     class DocCommentClass { public function nope(): void {} }
 *     interface DocIfc extends Nothing {}
 *     $unterminated = "a quote that never closes
 *
 * @param  array{a: int, b: string} $shape  array shapes carry braces
 * @return array<int, \Corp\Stress\Docs\Documented>
 * @throws \RuntimeException when { braces } appear in prose
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Documented
{
    /** @var array<string, int> { not a block } */
    private array $map = [];

    /** @var string a docblock ending in a backslash \\ */
    private string $escaped = '';

    // A line comment with a lookalike: class LineCommentClass {}
    # A hash comment with another: trait HashTrait { use Nothing; }

    /*
     * A block comment holding an unbalanced brace {
     * and a fake declaration: enum BlockEnum: string { case A = 'a'; }
     */
    public function documented(int $n): int
    {
        return $n;
    }

    /**
     * @param callable(int): int $fn
     */
    public function withCallableDoc(callable $fn): int
    {
        return $fn(1);
    }
}

/** @deprecated a one-line docblock immediately before a declaration */
trait DocumentedTrait
{
    /** @var int */
    private int $count = 0;
}

/* not a docblock, still a comment: interface Skipped {} */
enum DocumentedEnum: string
{
    /** @var string the first case */
    case First = 'first';
    case Second = 'second';
}
