<?php

namespace Acme\Order\Model;

/**
 * KNOWN DIVERGENCE — this golden is still WRONG in three places, on purpose.
 *
 * Verified by compiling this exact module with real Magento (Mage-OS 3.1.0)
 * and diffing the generated Interceptor. Magento is right; magecommand is not
 * yet. Re-bless only after fixing, and the diff should move TOWARD Magento.
 *
 *   expression        Magento          magecommand            status
 *   ----------------  ---------------  ---------------------  -----------
 *   Zebra&Alpha       \Alpha&\Zebra    \Zebra&\Alpha          OPEN
 *   -0.0              -0               0                      OPEN
 *   0.1 + 0.2         0.3              0.30000000000000004    OPEN
 *   2 ** 10           1024             1024                   fixed
 *   ['b' => G::Low]   [… G::Low]       [… G::Low]             fixed
 *
 * The two fixed ones were the severe pair: each dropped the default outright,
 * which turns an optional parameter into a required one and makes PHP treat
 * every earlier optional parameter as required too. The three still open are
 * byte divergences that would fail `di verify`, not breakage.
 *
 *   - Intersection members: Laminas SORTS them (IntersectionType.php:33-36);
 *     we emit source order. 213 intersection signatures in a stock install,
 *     so this is the widest-reaching of the three.
 *   - Magento renders floats at PHP's `precision` (14), not full round-trip
 *     precision, and keeps the sign of -0.0.
 *
 * Already matching Magento exactly: << % / . concat, a bare enum case, plain
 * arrays, 1.0 -> 1, and union member ordering (int|string|null).
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
