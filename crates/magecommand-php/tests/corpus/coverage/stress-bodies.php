<?php

declare(strict_types=1);

namespace Corp\Stress;

/**
 * Bodies are skipped, never parsed. This file exists to prove the skipper
 * survives what real code puts inside them and still finds the declarations
 * that follow — the property the sampled-vendor tier used to cover by bulk.
 *
 * Every hazard below is body interior: nested braces past the depth real
 * Magento reaches (11), heredocs and strings holding declaration lookalikes
 * and unbalanced braces, backticks, and a `?>` gap. A skipper that loses its
 * place shows up as a wrong declaration count in manifest.tsv.
 */
class NestedBodies
{
    public function deeplyNested(array $rows): array
    {
        $out = [];
        foreach ($rows as $row) {
            if (isset($row['a'])) {
                foreach ($row['a'] as $k => $v) {
                    if (is_array($v)) {
                        foreach ($v as $inner) {
                            if ($inner > 0) {
                                $out[] = array_map(function ($x) {
                                    return array_filter($x, function ($y) {
                                        if ($y) {
                                            return (function () {
                                                return ['n' => ['d' => ['deeper' => 1]]];
                                            })();
                                        }
                                        return false;
                                    });
                                }, [$inner]);
                            }
                        }
                    }
                }
            }
        }
        return $out;
    }

    public function heredocs(string $name): string
    {
        $sql = <<<SQL
            SELECT * FROM t WHERE payload = '{"class Foo { }": "{"}'
            SQL;
        $tpl = <<<HTML
            <div class="wrapper">{$name}</div>
            <?php class NotADeclaration { public function nope() {} } ?>
            HTML;
        $raw = <<<'NOWDOC'
            class AlsoNotADeclaration { { { unbalanced on purpose
            NOWDOC;

        return $sql . $tpl . $raw;
    }

    public function stringsWithBraces(array $arr): array
    {
        $a = "opening { brace in double quotes";
        $b = 'closing } brace in single quotes';
        $c = "interpolated {$arr['key']} and {$this->field}";
        $d = "escaped \" quote then } brace";
        $e = 'trailing backslash before quote \\';

        return [$a, $b, $c, $d, $e];
    }

    public function matchAndArrows(int $n): callable
    {
        $f = match (true) {
            $n > 10 => static fn (int $x): int => $x * 2,
            $n > 5 => static function (int $x): int {
                return $x + 1;
            },
            default => static fn (int $x): int => $x,
        };

        return $f;
    }

    private string $field = 'value';
}

/**
 * A second declaration after the noise: if the skipper mis-counts a brace
 * above, this one is swallowed and the manifest row for this file changes.
 */
final class AfterTheNoise
{
    public function proves(): bool
    {
        return true;
    }
}

interface StillFound
{
    public function reached(): void;
}
