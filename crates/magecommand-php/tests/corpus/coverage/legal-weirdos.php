<?php
declare(strict_types=1);

// coverage/legal-weirdos.php — reserved/semi-reserved words as method names,
// a declaration lookalike inside a string, one inside a comment, one inside
// a heredoc, an HTML gap between declarations, and top-level code shapes
// (registration-style calls). None of the lookalikes may produce a
// declaration or an issue.

namespace Corp\Weirdos;

class ReservedMethodNames
{
    public function list(): array
    {
        return [];
    }

    public function fn(): string
    {
        return 'fn';
    }

    public function match(string $x): string
    {
        return $x;
    }

    public function readonly(): static
    {
        return $this;
    }

    public function enum(): self
    {
        return $this;
    }
}

class Lookalikes
{
    public string $snippet = 'class Fake { public function fake() {} }';

    public function strings(): void
    {
        $comment = "// class Commented { }";
        $code = "interface InString extends Nope {}";
        $interp = "prefix {$this->snippet} class Interp {}";
    }

    public function heredocLookalike(): void
    {
        $doc = <<<DOC
        class HeredocClass { public function hidden() {} }
        DOC;
    }
}

/* class CommentBlock { protected function hidden(): void {} } */

// trait CommentTrait {}

$registration = ComponentRegistrar::register(ComponentRegistrar::MODULE, 'Corp_Weirdos', __DIR__);

?><h1>gap</h1><?php

class AfterGap {}
