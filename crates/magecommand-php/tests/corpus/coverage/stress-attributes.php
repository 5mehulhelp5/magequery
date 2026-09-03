<?php

declare(strict_types=1);

namespace Corp\Stress\Attr;

use Corp\Attributes\Marker;
use Corp\Attributes\Marker as Aliased;

/**
 * Attribute density and variety — the one construct the sampled-vendor tier
 * held in quantity (104 files). Class-level names must resolve to FQCNs;
 * method/parameter attributes must parse without reaching the output.
 */
#[Marker]
#[Aliased]
#[\Corp\Attributes\Absolute(1, 'two', [3, 4])]
#[WithArgs(name: 'x', items: ['a' => 1, 'b' => [2, 3]], flag: true)]
final class Attributed
{
    #[MethodAttr]
    #[MethodAttr(']')]
    public function bracketInString(
        #[ParamAttr] int $a,
        #[ParamAttr(1)] string $b = '}',
    ): void {
    }

    #[Multi(
        first: 'a',
        second: ['nested' => ['deep' => true]],
    )]
    public function multiLine(): void
    {
    }
}

#[Grouped, AlsoGrouped]
#[Grouped(1), AlsoGrouped(2)]
class GroupedAttributes
{
    #[Marker]
    public const TAGGED = 'tagged';

    public function m(): void
    {
    }
}

#[Marker]
interface AttributedInterface
{
}

#[Marker]
enum AttributedEnum: int
{
    #[Marker]
    case One = 1;
    case Two = 2;
}

#[Marker]
trait AttributedTrait
{
}
