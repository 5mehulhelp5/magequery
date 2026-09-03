# magecommand-php: committed corpus plan

## Problem

`magecommand-php` — the substrate under every magecommand build (CAS parse
objects, detection scan, engine arguments) — has exactly two validation nets:

1. **Unit tests** in `lib.rs`/`parse.rs`/`constexpr.rs`: hand-written snippets,
   always on, but narrow (each added only after a bug was found).
2. **Big-install tests** (`tests/corpus.rs`, `examples/differential.rs`):
   require `/home/jelle/mg-install-310` (685 MB vendor, 64k PHP files), so they
   are `#[ignore]`d — they run on one machine and nowhere else. CI has never
   executed either.

A regression that slips between the unit snippets (e.g. a construct family no
snippet covers) is invisible until someone happens to run the ignored test
against their local install.

## Goal

A **committed, byte-stable corpus of test PHP files** inside the crate:
`cargo test -p magecommand-php` validates the parser with zero environment, on
every machine and in CI.

Non-goals:

- Replacing the big-install goldens elsewhere (`static_deploy`, `minify`,
  jstranslation, fulltree) — those stay env-gated; the real install remains the
  reference for byte-exact *output* work.
- Byte-exact *render* goldens of parse output (snapshot files). The manifest
  digest (below) is the regression net; a `--bless` affordance covers
  intentional changes.

## Locked decisions

- **Location**: `crates/magecommand-php/tests/corpus/`. `tests/corpus.rs` is
  rewritten into the committed runner (always on, no env var).
- **Two parts** (a vendored tier was built and then removed — see Tier B):
  - `coverage/` — hand-written, one file per construct family (below), plus
    `stress-*.php` for shapes rather than constructs. This is the *precise*
    net: manifest pins exact declaration counts per file.
  - `manifest.tsv` + `xfail.txt` — the gate.
- **Zero-issue gate.** The committed corpus is curated, so the big-install
  `< 1% files with issues` bar becomes **0** (except `xfail.txt`). A new issue
  = either a parser bug (fix it) or a genuinely-unsupported construct (add the
  file to xfail with a one-line reason — the list is pinned exactly, and may
  only shrink).
- **Manifest per file**: `path  decls  methods  consts  cases  sha256`.
  The digest covers a small stable `fingerprint(meta)` rendered *by the test*
  (fqcn, kind, flags, method signatures, consts) — NOT `{:?}` of `ClassMeta`,
  whose Debug changes with every model edit and would force full re-blessing.
  Intentional parser changes re-bless via `MAGECOMMAND_CORPUS_BLESS=1 cargo
  test -p magecommand-php --test corpus`, which rewrites the manifest and
  shows a diff.
- **No vendored code, so no provenance machinery**: every corpus file is
  written for this repo, which removes the sampler, the pool digest, the
  `SOURCES.txt` hash ledger and the `VENDOR.txt` count check along with it.
- **Differential harness repointed**: `differential.rs` defaults to the
  committed corpus (`coverage/`), still overridable by arg/`MAGECOMMAND_CORPUS`
  for the full-install run. The corpus is self-contained, so corpus mode needs
  no reflection root at all. PHP-reflection ground truth still needs bougie +
  PHP, so it stays a non-CI affordance — but it can now run anywhere PHP
  exists, not only next to a 685 MB install.
- **Big-install smoke stays**: the current `parse_entire_corpus` keeps its
  `< 1%` gate and throughput print, but drops `#[ignore]` in favor of a
  runtime skip when the root is absent. It runs *only* when
  `MAGECOMMAND_CORPUS` names an install — no hardcoded default, which
  otherwise made a plain `cargo test` walk 685 MB on whichever machine had
  that directory, and made a mistyped root pass having parsed nothing. The
  committed corpus is the CI gate; this remains the local deep net.
- **Library stays memchr-only**: manifest hashing + sampling tooling are
  dev-deps / shell-script side; `magecommand-php` gains no runtime deps.

## Tier A — `coverage/` (hand-written, exact expectations)

One file per construct family; the manifest pins declaration/method/const
counts exactly. besides the obvious matrix (namespaces braced+statement,
use/simple/aliased/group, class/interface/trait/enum + every modifier), the
families that earn their own file because they are known sharp edges:

- **attributes** — with args (strings incl. `*/` and `#[` inside them, arrays,
  class consts, enum cases), repeated, on every position (class, method, prop,
  param, enum case, interface).
- **constants** — typed consts, expressions (arithmetic, concat, `Class::CONST`
  chains, arrays, enum cases), the constexpr evaluator's whole surface.
- **promoted constructor properties** — incl. readonly-promoted, defaults.
- **signature types** — nullable, unions, intersections (incl. DNF), variadics,
  by-ref, `static`/`never`/`true`/`false`/`mixed` returns, defaults incl.
  heredoc/nowdoc and `new` in initializers.
- **trait adaptations** — `insteadof`/`as`, abstract trait methods.
- **anonymous classes**, **alt-syntax `class_exists(...):` guard** (the TIG
  PostNL shim — currently only a unit snippet), **generated-code shapes**:
  one Factory, one Interceptor, one Proxy copied in shape from the 310
  install (the detection phase's `…Factory`/`\Proxy` world; `dev/tests` has
  none of these).
- **legal weirdos** — reserved words as method names (`list()`, `fn()`,
  `match()`), `declare(strict_types=1)` + comments, declaration-lookalikes in
  strings/comments (a `class X {` inside a string must not parse as one).
- **php84** — property hooks / asymmetric visibility: the parser must not
  panic and must classify-or-issue per the no-guess contract. Whatever it does
  gets pinned; if it issues, the file is the founding `xfail.txt` entry with
  the decision written in `NOTES.md`.

## Tier B — sampled real code: built, measured, removed

Built as planned (655 files / 2.5 MiB sampled from
`upstream/mageos-magento2`'s test trees by a deterministic sampler with a
pool digest and a sha256 ledger), then removed after measuring what it
bought. The numbers, kept here so the decision is not re-litigated blind:

- **79,422 lines to pin 504 declarations.** 151 of the 655 files declared
  nothing at all; ~219 held at most one declaration and one method.
- **No construct that `coverage/` lacks.** Zero enums, zero DNF or
  intersection types, zero trait-adaptation blocks, zero grouped consts,
  zero `static` returns, zero property hooks. Magento's test trees are
  PHP 7-era; the hand-written tier is strictly richer on the modern surface.
- **Zero xfail entries.** The only pinned exception was, and remains, in a
  hand-written file.
- **Standing costs**: a 253-line sampler, a 655-row `SOURCES.txt`, a
  `VENDOR.txt` count check — machinery whose only job was keeping a copy of
  someone else's tree trustworthy. The tier also arrived carrying 33
  machine-local files, four of them absolute symlinks into a developer's
  `$HOME`: exactly what `operations/sync` rejects.

What it genuinely exercised was **bulk** — files to 46 KB, brace nesting to
depth 11, heredocs and attributes in quantity — which is a body-skipper
stress, not a declaration-parser one. That is now covered on purpose by
`coverage/stress-bodies.php`, `stress-docblocks.php` and
`stress-attributes.php`, each placing a declaration *after* the noise so a
skipper that loses its place changes a manifest row.

Breadth against real code stays where it costs nothing to keep: the
`parse_entire_corpus` smoke and `examples/differential.rs`, both of which
run against a real install. A construct they turn up that this corpus
cannot express becomes a new synthetic file here.

## Test wiring (`tests/corpus.rs`)

1. Walk `corpus/coverage/`, parse every file. Manifest keys are `/`-joined
   regardless of host separator, so a manifest blessed on one machine checks
   on every other (Windows included).
2. Assert: no panics; issues ⊆ `xfail.txt` **exactly** (both directions —
   a fixed xfail file must be removed from the list, not forgotten).
3. Per file: counts + fingerprint digest match `manifest.tsv`; on mismatch,
   print old vs new fingerprint for that file (bounded, first N decls).
4. Summary line (files, KiB, decls, methods, xfail count) — printed, never
   asserted. Throughput belongs to the big-install smoke; the committed
   corpus is far too small to measure anything.
5. Big-install smoke: unchanged gate, runtime skip when the root is absent.

## Build order

1. `coverage/` files + manifest format + rewritten `tests/corpus.rs` with
   bless mode. (Green gate from day one.)
2. ~~`vendor-php-corpus.sh` + first vendored set~~ — built, measured,
   removed (Tier B above). Replaced by the three `stress-*.php` files.
3. `xfail.txt` policy decision on the php84 file (each entry is a probable
   parser bug worth an issue-line in `NOTES.md`).
4. Repoint `differential.rs`; drop `#[ignore]` in favor of runtime skip.
5. Corpus test into the crate's normal `cargo test` flow (it already is one);
   note in `.plans/magecommand.md` that the parser's always-on net is now
   corpus-backed, and CI can call it from any runner with no env.

## Future (out of scope)

- cargo-fuzz over the corpus (planned in `magecommand.md` §correctness
  harness) seeds directly from it.
- Per-crate consumers (`magecommand-engine` args, `static_deploy` detection)
  may add projections over the same corpus later; not now.

## As built

- **Manifest columns**: `path  decls  methods  consts  cases  file_sha256
  fp_sha256` — the review's pin: `file_sha256` (corpus file bytes) and
  `fp_sha256` (the stable fingerprint) are separate, so accidental corpus
  edits and parser drift are distinguishable failures. The fingerprint is
  versioned (`FINGERPRINT_VERSION`, now **2**) and the version is *enforced*,
  not merely documented: it is written into `manifest.tsv` as a
  `# fingerprint-version:` data line and asserted on read, so digests from
  two renderings can never be compared. Bless mode deliberately reads any
  version — it has to, to show the diff — and announces the transition.
  v2 added trait adaptations (v1 left `trait-adaptations.php` inert: a
  parser dropping all six adaptations produced a byte-identical digest) and
  dropped a param column that restated two others.
- **Bless is checked by value**, not by presence: `MAGECOMMAND_CORPUS_BLESS`
  must be `0` or `1`. Presence alone meant a variable left exported in a
  shell — or set to `0` believing that disabled it — turned every later
  `cargo test` into a rubber stamp that rewrote the manifest and passed.
- **Manifest keys are `/`-joined** from path components rather than
  `to_string_lossy`, so a manifest blessed on Linux checks on Windows
  instead of failing every row.
- **Tier B removed after measurement** — the sampler ran, the tier was
  committed, and it bought nothing `coverage/` did not already have (numbers
  under Tier B above). Sampler, `SOURCES.txt`, `VENDOR.txt` and the
  vendor-count gate check went with it; three `stress-*.php` files replace
  the one thing it did exercise, brace/heredoc/attribute bulk.
- **Corpus set**: 18 files, 27.9 KiB, 73 declarations, 89 methods — all
  hand-written. Zero-issue gate holds everywhere except the expected php84
  case: the only issue in the whole corpus is the promoted-parameter hook in
  `coverage/php84-hooks.php`, the founding xfail entry (decision in
  `corpus/NOTES.md`).
- **Differential harness**: install-only, and deliberately so. Pointing it
  at the synthetic corpus does not work — those files reference parents and
  traits that do not exist (a `class_exists` shim extends a missing
  dependency on purpose), and PHP fatals rather than reflecting a class
  whose parent is undefined. Tried it: 23 fatals and 2 false mismatches out
  of 73 declarations. Reflection ground truth needs real, loadable code, so
  the pool is a root (arg or `MAGECOMMAND_CORPUS`) with no default, and
  `MAGECOMMAND_DIFF_ROOT` overrides the reflection root.
  The child process runs in the reflection root, never the pool: Magento's
  bootstrap is cwd-relative, and pointing it at the pool is what made
  `bougie run php` materialise its shim tree inside `tests/corpus/` (four
  absolute symlinks into `$HOME` reached the first commit that way).
  `tests/reflect.php` loads non-autoloadable classes with `require_once`
  **in its own scope** — Magento's `_files` fixtures assign top-level
  `$file`, which would otherwise overwrite the read loop's state and
  desynchronise records from requests. That fallback widened the comparison
  from its M1 state and surfaced version-gated polyfill files (php-amqplib
  BigInteger, Relay traits, symfony-cache exceptions), skipped
  **structurally** via the parser's `conditional` flag rather than by
  hardcoded filename. Result: 43,731 classes compared, 0 mismatches.
- **The `conditional` skip is known to over-skip, and stays anyway.** What
  should be skipped is a file declaring the same FQCN in several branches;
  `conditional` is also true of a single-branch guard reflection agrees
  with exactly. Measured on the 310 install: 118 conditional declarations
  out of 51,030 — 29 genuinely redeclared, 89 skipped for no reason (0.2%
  of the denominator). It is the only signal available: `parse_file` keeps
  the `if` branch and never descends into the `else`, so by the time the
  harness holds a `ClassMeta` the second declaration does not exist to
  count. Narrowing it means teaching the parser to record that it passed
  over a redeclaration — a model change, deliberately not made here.
- **Byte stability**: `crates/magecommand-php/tests/corpus/** -text` in
  `.gitattributes` (same rule as the LESS fixtures).
