<?php
// coverage/attributes.php — attribute groups in every position and shape the
// detection scan must survive: plain names, aliases, args with strings
// containing `*/` and `#[` and `]`, nested arrays, class constants, enum
// cases, repeated groups, and every position (class, interface, enum,
// method, property, parameter, enum case, constant). Member attributes are
// consumed (not modeled) — the fingerprint pins the class-level attribute
// names and that the members survive intact.

namespace Corp\Attributes;

use Corp\Modifiers\BaseIfc;

#[\Attribute]
class Marker
{
}

#[Marker]
#[\Corp\Attributes\Marker(']*/ #[ keep', [1, 2], PHP_INT_MAX, self::TOKEN)]
#[Marker(BaseIfc::class, ['a' => 1, 'b' => [2, 3]])]
class Attributed
{
    public const TOKEN = 'corp-token';

    #[Marker]
    public string $prop = 'default';

    #[Marker('arg-one')]
    #[\Corp\Attributes\Marker(arg: 'two')]
    public function method(#[Marker] string $param, ?int $opt = null): void
    {
    }

    #[Marker([Marker::class, 'other'])]
    private function hidden(): int
    {
        return 1;
    }
}

#[Marker]
#[\Corp\Attributes\Marker]
interface AttributedIfc
{
    #[Marker]
    public function ifcMethod(): void;
}

enum AttributedEnum: string
{
    #[Marker('case-arg')]
    case One = 'one';

    case Two = 'two';
}

#[Marker('class-' . 'concat', markerClass: Marker::class)]
final class AttributedCtor
{
    public function __construct(
        #[Marker] public readonly string $id,
        #[\Corp\Attributes\Marker(1)] ?int $n = null,
    ) {
    }
}
