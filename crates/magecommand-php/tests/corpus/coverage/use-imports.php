<?php
// coverage/use-imports.php — every import form: simple, aliased, group,
// group with function/const members, function/const top-level forms, and a
// leading-backslash import. The classes below use them in extends,
// implements, parameter types, return types, and constant values so the
// fingerprint pins the resolved names.

namespace Corp\Imports;

use Vendor\Simple\PlainClass;
use Vendor\Aliased\LongOriginalName as ShortAlias;
use Vendor\Group\{FirstGrouped, SecondGrouped as Renamed, Nested\Deeper};
use Vendor\Mixed\{MixedOne, function mixed_helper, const MIXED_FLAG};
use function Vendor\Func\imported_function;
use const Vendor\Const\IMPORTED_CONST;
use \Vendor\Backslashed\LeadingSlash;

interface UsesImports extends ShortAlias
{
    public const FROM_CONST = IMPORTED_CONST;

    public function make(PlainClass $p, ?Deeper $d = null): FirstGrouped;
}

abstract class UsesGrouped implements Renamed, LeadingSlash
{
    use \Vendor\TraitRef\SomeTrait;

    public function run(MixedOne $one, SecondGrouped $two): Deeper
    {
        return new Deeper();
    }
}
