<?php

namespace Acme\Order\Model;

/**
 * Composite type ordering, and defaults Magento EVALUATES rather than copies.
 *
 * Every line of this fixture's golden was verified byte-for-byte against a
 * real Mage-OS 3.1.0 compile of this exact module. It began as a probe that
 * found five divergences; all five are now fixed, and it stays as the
 * regression test for them:
 *
 *   Zebra&Alpha       -> \Alpha&\Zebra   Laminas SORTS intersection members,
 *                                        so declaration order is not kept.
 *                                        213 such signatures in a stock install.
 *   string|int|null   -> int|string|null  unions sort by Laminas' precedence
 *                                        table, not alphabetically.
 *   -0.0              -> -0               the sign survives; `f as i64` ate it.
 *   0.1 + 0.2         -> 0.3              PHP renders floats at `precision`
 *                                        (14 significant digits), not at
 *                                        Rust's shortest round-trip.
 *   2 ** 10           -> 1024             `**` binds tighter than unary minus
 *                                        and is right-associative.
 *   ['b' => G::Low]   -> [… G::Low]       an enum case cannot be folded, so
 *                                        the array is rendered verbatim
 *                                        rather than the default being dropped.
 *
 * The last two mattered most: each dropped the default outright, which turns
 * an optional parameter into a required one and makes PHP treat every earlier
 * optional parameter as required too.
 */
class Typed
{
    public const LIMIT = 7;

    public function inter(Zebra&Alpha $both): Zebra&Alpha { return $both; }
    public function uni(string|int|null $m): bool|int|string { return true; }

    public function defs(
        float $round = 1.0,
        float $neg = -0.0,
        float $sum = 0.1 + 0.2,
        int $pow = 2 ** 10,
        int $shift = 1 << 4,
        int $mod = 7 % 3,
        float $div = 7 / 2,
        array $arrFloat = ['a' => 1.5],
        array $arrEnum = ['b' => Grade::Low],
        array $arrPlain = ['c' => 1],
        Grade $case = Grade::High,
        string $cat = self::LIMIT . '-suffix'
    ): void {
    }
}
