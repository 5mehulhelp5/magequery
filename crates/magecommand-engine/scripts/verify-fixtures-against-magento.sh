#!/usr/bin/env bash
#
# Ground-truth the compile fixtures against real Magento.
#
# `cargo test -p magecommand-engine --test compile` pins what magecommand
# emits. It cannot say whether that is CORRECT. This does: it installs every
# fixture module into a throwaway copy of a real install, runs
# `bin/magento setup:di:compile`, and diffs Magento's own output against the
# fixture goldens — metadata entries filtered to each fixture's namespace, and
# every generated class byte for byte.
#
# Not a test: it needs a Magento install, PHP and bougie, so it never runs in
# CI. Run it when adding or changing a fixture. A fixture that has never been
# through this pins behaviour nobody has checked.
#
#   ./verify-fixtures-against-magento.sh /path/to/magento-install
#
# The install is COPIED first and never written to. The copy costs ~1 GB.
#
# Four fixture defects this found the first time it ran, none of which any
# amount of staring at the goldens would have surfaced:
#   - no registration.php, so Magento never loaded the modules at all and
#     silently compiled nothing for them;
#   - di.xml using xsi:type without declaring the xsi namespace, which
#     DOMDocument rejects (quick-xml matches the attribute name literally and
#     does not care);
#   - an <argument> with no xsi:type, which aborts the real compile outright
#     and appears zero times in a 685 MB install;
#   - a reference to a generated kind with no emitter, likewise fatal.
set -euo pipefail

SRC=${1:?usage: verify-fixtures-against-magento.sh <magento-root>}
HERE=$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)
FIX="$HERE/../tests/compile-fixtures"
WORK=${MAGECOMMAND_ORACLE_DIR:-/tmp/magecommand-oracle}
SANDBOX="$WORK/install"

# Fixtures real Magento cannot compile. Each must say why in its own source.
SKIP_INSTALL=("never-return-is-magentos-own-bug")

skip() { local n=$1; for s in "${SKIP_INSTALL[@]}"; do [ "$n" = "$s" ] && return 0; done; return 1; }

mkdir -p "$WORK"
if [ ! -d "$SANDBOX" ]; then
    echo "copying $SRC -> $SANDBOX (once; ~1 GB)"
    cp -a "$SRC" "$SANDBOX"
fi

# ---- install every fixture module -----------------------------------------
rm -rf "${SANDBOX:?}/app/code/Acme"
python3 - "$SANDBOX" <<'PY'
import pathlib, re, sys
p = pathlib.Path(sys.argv[1]) / 'app/etc/config.php'
p.write_text(re.sub(r"\n\s*'Acme_[A-Za-z]+' => [01],", "", p.read_text()))
PY

mods=()
for case in "$FIX"/*/; do
    name=$(basename "$case")
    [ -d "$case/in/app/code" ] || continue
    skip "$name" && continue
    cp -r "$case/in/app/code/"* "$SANDBOX/app/code/"
    while IFS= read -r m; do mods+=("$m"); done < <(
        find "$case/in/app/code" -name module.xml -printf '%h\n' |
            sed 's|.*/app/code/||; s|/etc$||; s|/|_|'
    )
done
printf '%s\n' "${mods[@]}" | sort -u > "$WORK/modules.txt"
python3 - "$SANDBOX" "$WORK/modules.txt" <<'PY'
import pathlib, re, sys
mods = [m.strip() for m in open(sys.argv[2]) if m.strip()]
p = pathlib.Path(sys.argv[1]) / 'app/etc/config.php'
ins = "".join(f"\n        '{m}' => 1," for m in mods)
p.write_text(re.sub(r"('modules'\s*=>\s*\[)", lambda m: m.group(1) + ins, p.read_text(), count=1))
print(f"enabled {len(mods)} fixture modules")
PY

# ---- Magento's own compile -------------------------------------------------
echo "running setup:di:compile …"
( cd "$SANDBOX" && rm -rf generated/code generated/metadata &&
  bougie run php bin/magento setup:di:compile ) | tail -1

# ---- helpers ---------------------------------------------------------------
cat > "$WORK/subset.php" <<'PHP'
<?php
// Entries of a compiled metadata file whose key starts with $prefix, in a
// stable var_export form so two compilers diff exactly.
$data = require $argv[1];
$out = [];
foreach (['arguments', 'preferences', 'instanceTypes', 'nonLazyTypes'] as $sec) {
    if (!isset($data[$sec]) || !is_array($data[$sec])) { continue; }
    $sub = [];
    foreach ($data[$sec] as $k => $v) {
        if (str_starts_with((string) $k, $argv[2])) { $sub[$k] = $v; }
    }
    ksort($sub);
    $out[$sec] = $sub;
}
var_export($out);
echo "\n";
PHP

extract() { # golden, "code/Foo.php" -> the file body, without the separator
    awk -v want="===== file: $2 =====" '
        $0 == want { on=1; next } on && /^===== / { exit } on { print }' "$1" |
    awk '{ l[NR]=$0 } END { n=NR; if (l[n]=="") n--; for (i=1;i<=n;i++) print l[i] }'
}

# ---- compare ---------------------------------------------------------------
meta_ok=0; meta_bad=0; code_ok=0; code_bad=0
for case in "$FIX"/*/; do
    name=$(basename "$case")
    [ -f "$case/expected.txt" ] || continue
    skip "$name" && { echo "skipped (documented): $name"; continue; }

    extract "$case/expected.txt" "metadata/global.php" > "$WORK/g.php"
    bad=0
    while IFS= read -r pre; do
        [ -n "$pre" ] || continue
        bougie run php "$WORK/subset.php" "$SANDBOX/generated/metadata/global.php" "$pre" 2>/dev/null | grep -v '^Resolved' > "$WORK/o.txt"
        bougie run php "$WORK/subset.php" "$WORK/g.php" "$pre" 2>/dev/null | grep -v '^Resolved' > "$WORK/m.txt"
        if ! diff -q "$WORK/o.txt" "$WORK/m.txt" >/dev/null; then
            bad=1; echo "=== METADATA DIFFERS: $name [$pre] ==="; diff "$WORK/o.txt" "$WORK/m.txt" | head -25
        fi
    done < <(find "$case/in/app/code" -name module.xml -printf '%h\n' 2>/dev/null |
             sed 's|.*/app/code/||; s|/etc$||; s|/|\\|' | sort -u)
    [ $bad -eq 0 ] && meta_ok=$((meta_ok+1)) || meta_bad=$((meta_bad+1))

    while IFS= read -r f; do
        [ -n "$f" ] || continue
        if [ ! -f "$SANDBOX/generated/code/$f" ]; then
            echo "=== ONLY MAGECOMMAND EMITS: $f ($name) ==="; code_bad=$((code_bad+1)); continue
        fi
        extract "$case/expected.txt" "code/$f" > "$WORK/c.php"
        if diff -q "$SANDBOX/generated/code/$f" "$WORK/c.php" >/dev/null; then
            code_ok=$((code_ok+1))
        else
            code_bad=$((code_bad+1)); echo "=== CODE DIFFERS: $f ($name) ==="
            diff "$SANDBOX/generated/code/$f" "$WORK/c.php" | head -20
        fi
    done < <(grep -o '^===== file: code/[^ ]*' "$case/expected.txt" | sed 's|^===== file: code/||')
done

echo
echo "metadata: $meta_ok verified, $meta_bad divergent"
echo "code:     $code_ok verified, $code_bad divergent"
[ $meta_bad -eq 0 ] && [ $code_bad -eq 0 ]
