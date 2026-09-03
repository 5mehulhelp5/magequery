<?php
// coverage/constants.php — the constexpr surface: typed constants, grouped
// declarations, every visibility, and the expression families (arithmetic,
// concatenation, Class::CONST chains, arrays incl. nested, enum cases,
// scalar enums in interface/enum position). Values are pinned verbatim
// (whitespace-collapsed raw text) by the fingerprint.

namespace Corp\Constants;

class ConstExpr
{
    public const PLAIN = 'plain';
    public const int TYPED = 42;
    private const string TYPED_STR = 'typed';
    protected const float FLOATY = 1.5;
    public const ARITH = 2 + 3 * 4;
    public const CONCAT = 'left' . 'right';
    public const CHAIN = self::PLAIN . self::PLAIN;
    public const REFERENCE = \Corp\Modifiers\IntBacked::First;
    public const NESTED_REF = self::REFERENCE;
    public const ARRAY_LIT = ['a' => 1, 'b' => [2, 3], self::PLAIN];
    public const KEYED = [self::PLAIN => 1, 2 => 'two'];
    public const CLASS_CONST = \Corp\Attributes\Marker::class;
    public const BOOLS = true;
    public const NULLY = null;

    public const grouped_one = 1, grouped_two = 2;

    public function useConst(): int
    {
        return self::ARITH;
    }
}

interface IfcConstants
{
    public const IFC_VAL = 'ifc';
    public const int IFC_TYPED = 7;

    public function ifcMethod(): void;
}

enum ConstEnum: string
{
    case Small = 's';
    case Large = 'l';

    public const DEFAULT_CASE = self::Small;
    public const ALL = [self::Small, self::Large];
    public const DERIVED = \Corp\Constants\ConstEnum::Large;
}

trait TraitConstants
{
    public const TRAIT_CONST = 'from-trait';
    private const array TRAIT_ARRAY = ['x' => 1];
}
