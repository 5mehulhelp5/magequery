//! Differential harness: prove the parser against PHP reflection.
//!
//! Parses a pool of PHP files, then asks PHP itself (via
//! `bougie run php tests/reflect.php` inside a Magento checkout) to reflect
//! every declaration we found, and diffs the two views: kind, flags, parent,
//! interfaces, methods, signatures, types (canonical form), constants, enum
//! cases. PHP runs only here, in test infrastructure — never in the tool.
//!
//! The pool is a real Magento install — a root as the first argument, or
//! `MAGECOMMAND_CORPUS`. There is no default: this walks a 685 MB tree.
//! `MAGECOMMAND_DIFF_ROOT` overrides the reflection root when it differs
//! from the pool.
//!
//! It is deliberately NOT pointed at `tests/corpus/`. That corpus is
//! synthetic and written to be *parsed*, not loaded: files reference parents
//! and traits that do not exist (`class_exists` shims extend a missing
//! dependency on purpose), and PHP cannot reflect a class whose parent is
//! undefined — it fatals. Reflection ground truth needs real, loadable code.
//! The parser's net over the corpus is `tests/corpus.rs`; this is its net
//! over reality.
//!
//! Files are presented to PHP at the path we parsed, so the `realpath`
//! shadowing check compares like with like. Stays a non-CI affordance: it
//! needs bougie + PHP.
//!
//! ```sh
//! cargo run --release -p magecommand-php --example differential -- /home/jelle/mg-install-310
//! MAGECOMMAND_CORPUS=/home/jelle/mg-install-310 \
//!   cargo run --release -p magecommand-php --example differential
//! ```

use std::collections::BTreeMap;
use std::fs;
use std::io::{BufRead, BufReader, Write};
use std::path::{Path, PathBuf};
use std::process::{Command, Stdio};

use magecommand_php::{ClassKind, ClassMeta, MethodMeta};
use rayon::prelude::*;
use serde_json::Value;

const BATCH: usize = 8000;

fn main() {
    // No default root: a hardcoded one runs the wrong install silently, and
    // a mistyped one used to fall through to it. Both are errors here.
    let pool_root = std::env::args()
        .nth(1)
        .map(PathBuf::from)
        .or_else(|| std::env::var_os("MAGECOMMAND_CORPUS").map(PathBuf::from))
        .expect(
            "usage: differential <magento-root>  (or set MAGECOMMAND_CORPUS). \
             The committed corpus is not a valid pool — it is synthetic and \
             unloadable by design; see this file's module docs.",
        );
    assert!(pool_root.is_dir(), "no such install root: {}", pool_root.display());
    let diff_root = std::env::var_os("MAGECOMMAND_DIFF_ROOT")
        .map(PathBuf::from)
        .unwrap_or_else(|| pool_root.clone());
    assert!(
        diff_root.join("vendor/autoload.php").is_file(),
        "reflection root has no vendor/autoload.php: {}",
        diff_root.display()
    );

    // 1. Parse the pool.
    let mut files = Vec::new();
    for sub in ["vendor", "app", "lib", "generated"] {
        collect_php(&pool_root.join(sub), &mut files);
    }
    eprintln!("parsing {} files …", files.len());
    let parsed: Vec<(PathBuf, ClassMeta)> = files
        .par_iter()
        .filter_map(|path| {
            let src = fs::read(path).ok()?;
            let meta = magecommand_php::parse_file(&src);
            Some(
                meta.declarations
                    .into_iter()
                    .map(|d| (path.clone(), d))
                    .collect::<Vec<_>>(),
            )
        })
        .flatten()
        .collect();
    // FQCN -> (file, meta); duplicates (test fixtures) resolved by the
    // shadowed check on the PHP side, keep the first.
    let mut by_fqcn: BTreeMap<String, (PathBuf, ClassMeta)> = BTreeMap::new();
    for (path, decl) in parsed {
        by_fqcn.entry(decl.fqcn.clone()).or_insert((path, decl));
    }
    eprintln!("{} unique declarations", by_fqcn.len());

    // 2. Reflect in batches. Files go to PHP at the path we parsed, so
    // `reflect.php`'s `realpath` shadow check compares the file it loaded
    // against the file we read.
    let script = Path::new(env!("CARGO_MANIFEST_DIR")).join("tests/reflect.php");
    let entries: Vec<(&String, &(PathBuf, ClassMeta))> = by_fqcn.iter().collect();
    let mut stats: BTreeMap<&'static str, usize> = BTreeMap::new();
    let mut mismatches: Vec<String> = Vec::new();
    let mut compared = 0usize;

    // Magento's bootstrap resolves paths against the process cwd, so the
    // child runs in the reflection root — never in the pool. (Pointing it at
    // the corpus made `bougie run php` materialise its shim tree *inside*
    // tests/corpus/, which is how a machine-local vendor/bougie/ directory
    // came to be committed once.)
    let child_cwd = &diff_root;

    for chunk in entries.chunks(BATCH) {
        // A PHP fatal (uncatchable redeclare/inheritance error while loading
        // one class) kills the whole process. Recover by marking the killer
        // (the first class with no output) and resuming right after it.
        let mut start = 0usize;
        while start < chunk.len() {
            let rest = &chunk[start..];
            let mut child = Command::new("bougie")
                .args(["run", "php"])
                .arg(&script)
                .arg(&diff_root)
                .current_dir(child_cwd)
                .stdin(Stdio::piped())
                .stdout(Stdio::piped())
                .stderr(Stdio::null())
                .spawn()
                .expect("failed to spawn bougie run php");
            let mut stdin = child.stdin.take().unwrap();
            let input: String = rest
                .iter()
                .map(|(fqcn, (path, _))| {
                    format!("{}\t{}\n", fqcn, path.display())
                })
                .collect();
            let writer = std::thread::spawn(move || {
                let _ = stdin.write_all(input.as_bytes());
            });
            let stdout = BufReader::new(child.stdout.take().unwrap());
            let mut seen = 0usize;
            for line in stdout.lines() {
                let Ok(line) = line else { break };
                let Ok(rec) = serde_json::from_str::<Value>(&line) else {
                    continue;
                };
                seen += 1;
                let fqcn = rec["fqcn"].as_str().unwrap_or_default().to_owned();
                match rec["status"].as_str().unwrap_or("") {
                "ok" => {
                    let (path, ours) = &by_fqcn[&fqcn];
                    if ours.conditional || is_conditional_polyfill(path) {
                        // What we WANT to skip is narrower than this: a file
                        // that declares the same FQCN in several branches
                        // (`if (version_compare(…)) { trait T … } else
                        // { trait T … }`), where which branch PHP loads is a
                        // runtime fact and reflection legitimately disagrees.
                        //
                        // `conditional` over-skips: it is equally true of a
                        // single-branch guard whose one declaration
                        // reflection agrees with exactly. Measured on the
                        // 310 install: 118 conditional declarations out of
                        // 51,030, of which 29 are genuinely redeclared and
                        // 89 are single-declaration guards skipped for no
                        // reason — 0.2% of the denominator.
                        //
                        // It is nonetheless the only signal available here:
                        // `parse_file` keeps the `if` branch and never
                        // descends into the `else` (see the
                        // `*_keeps_if_branch` unit tests), so by the time the
                        // harness has a ClassMeta the second declaration no
                        // longer exists to count. Narrowing this predicate
                        // means teaching the parser to record that it passed
                        // over a redeclaration — a model change, not a
                        // harness one.
                        *stats.entry("conditional-declaration").or_default() += 1;
                    } else {
                        compared += 1;
                        compare(ours, &rec, &mut mismatches);
                    }
                }
                    "unloadable" => *stats.entry("unloadable").or_default() += 1,
                    "shadowed" => *stats.entry("shadowed").or_default() += 1,
                    "error" => *stats.entry("reflection-error").or_default() += 1,
                    _ => *stats.entry("unknown-status").or_default() += 1,
                }
            }
            writer.join().ok();
            let status = child.wait().expect("php did not exit");
            if status.success() && seen >= rest.len() {
                break;
            }
            // rest[seen] produced no output before the fatal: the killer.
            *stats.entry("fatal-on-load").or_default() += 1;
            start += seen + 1;
            eprint!("!");
        }
        eprint!(".");
    }
    eprintln!();

    // 3. Report.
    println!("compared {compared} classes against reflection");
    for (k, v) in &stats {
        println!("  {k}: {v}");
    }
    println!("mismatches: {}", mismatches.len());
    for m in mismatches.iter().take(2000) {
        println!("  {m}");
    }
    if mismatches.len() > 2000 {
        println!("  … {} more", mismatches.len() - 2000);
    }
    if !mismatches.is_empty() {
        std::process::exit(1);
    }
}

/// Files with hardcoded polyfill shapes beyond the conditional flag (the
/// two known alt-syntax shims, whose guard has no braces so `conditional`
/// stays false). Everything else rides the structural `ClassMeta.
/// conditional` check.
fn is_conditional_polyfill(path: &Path) -> bool {
    let s = path.to_string_lossy();
    s.ends_with("psy/psysh/src/VarDumper/Dumper.php")
        || s.ends_with("php-parser/lib/PhpParser/Internal/TokenPolyfill.php")
}

fn collect_php(root: &Path, out: &mut Vec<PathBuf>) {
    let Ok(entries) = fs::read_dir(root) else {
        return;
    };
    for entry in entries.flatten() {
        let path = entry.path();
        let Ok(ft) = entry.file_type() else { continue };
        if ft.is_dir() {
            let name = entry.file_name();
            if name == ".git" || name == "node_modules" {
                continue;
            }
            collect_php(&path, out);
        } else if path.extension().is_some_and(|e| e == "php") {
            out.push(path);
        }
    }
}

// ---- comparison ------------------------------------------------------------

fn compare(ours: &ClassMeta, theirs: &Value, out: &mut Vec<String>) {
    let fqcn = &ours.fqcn;
    let kind = match ours.kind {
        ClassKind::Class => "class",
        ClassKind::Interface => "interface",
        ClassKind::Trait => "trait",
        ClassKind::Enum => "enum",
        _ => "unknown",
    };
    if theirs["kind"].as_str() != Some(kind) {
        out.push(format!(
            "{fqcn}: kind {kind} vs {}",
            theirs["kind"].as_str().unwrap_or("?")
        ));
        return;
    }
    if kind == "class" {
        if ours.is_abstract != theirs["abstract"].as_bool().unwrap_or(false) {
            out.push(format!("{fqcn}: abstract flag"));
        }
        if ours.is_final != theirs["final"].as_bool().unwrap_or(false) {
            out.push(format!("{fqcn}: final flag"));
        }
        let our_parent = ours.extends.first().map(|s| s.to_ascii_lowercase());
        let their_parent = theirs["parent"].as_str().map(str::to_owned);
        if our_parent != their_parent {
            out.push(format!(
                "{fqcn}: parent {:?} vs {:?}",
                our_parent, their_parent
            ));
        }
    }
    // Direct implements (ours) must appear in reflection's transitive set;
    // for interfaces the extends list plays that role.
    let their_ifaces: Vec<String> = theirs["interfaces"]
        .as_array()
        .map(|a| {
            a.iter()
                .filter_map(|v| v.as_str())
                .map(str::to_owned)
                .collect()
        })
        .unwrap_or_default();
    let our_direct = if kind == "interface" {
        &ours.extends
    } else {
        &ours.implements
    };
    for iface in our_direct {
        if !their_ifaces.contains(&iface.to_ascii_lowercase()) {
            out.push(format!("{fqcn}: interface {iface} unknown to reflection"));
        }
    }

    // Methods.
    let their_methods = theirs["methods"].as_object();
    let empty = serde_json::Map::new();
    let their_methods = their_methods.unwrap_or(&empty);
    let mut ours_by_name: BTreeMap<String, &MethodMeta> = BTreeMap::new();
    for m in &ours.methods {
        ours_by_name.insert(m.name.to_ascii_lowercase(), m);
    }
    for name in their_methods.keys() {
        if !ours_by_name.contains_key(name) {
            out.push(format!("{fqcn}::{name}: missing from parser output"));
        }
    }
    // PHP 8.5 reflection resolves `self`/`parent` return/param types to
    // concrete class names; the source (and our parser) keeps the keyword.
    let mut subst: BTreeMap<String, String> = BTreeMap::new();
    subst.insert("self".to_owned(), fqcn.to_ascii_lowercase());
    if let Some(parent) = theirs["parent"].as_str() {
        subst.insert("parent".to_owned(), parent.to_owned());
    }
    for (name, m) in &ours_by_name {
        let Some(t) = their_methods.get(name) else {
            out.push(format!("{fqcn}::{name}: phantom method (reflection lacks it)"));
            continue;
        };
        compare_method(fqcn, name, m, t, &subst, out);
    }

    // Constants (names only — values await the const-expression evaluator).
    let mut our_consts: Vec<String> = ours.constants.iter().map(|c| c.name.clone()).collect();
    our_consts.sort();
    let their_consts: Vec<String> = theirs["constants"]
        .as_array()
        .map(|a| {
            a.iter()
                .filter_map(|v| v.as_str())
                .map(str::to_owned)
                .collect()
        })
        .unwrap_or_default();
    if our_consts != their_consts {
        out.push(format!(
            "{fqcn}: constants {our_consts:?} vs {their_consts:?}"
        ));
    }
    if kind == "enum" {
        let mut our_cases = ours.cases.clone();
        our_cases.sort();
        let mut their_cases: Vec<String> = theirs["cases"]
            .as_array()
            .map(|a| {
                a.iter()
                    .filter_map(|v| v.as_str())
                    .map(str::to_owned)
                    .collect()
            })
            .unwrap_or_default();
        their_cases.sort();
        if our_cases != their_cases {
            out.push(format!("{fqcn}: enum cases differ"));
        }
    }
}

fn compare_method(
    fqcn: &str,
    name: &str,
    ours: &MethodMeta,
    theirs: &Value,
    subst: &BTreeMap<String, String>,
    out: &mut Vec<String>,
) {
    use magecommand_php::Visibility;
    // Magic methods carry engine-implied signatures (`__toString(): string`)
    // that PHP 8.5 reflection reports even when the source declares none.
    let magic = name.starts_with("__");
    let vis = match ours.visibility {
        Visibility::Public => "public",
        Visibility::Protected => "protected",
        Visibility::Private => "private",
        _ => "unknown",
    };
    if theirs["v"].as_str() != Some(vis) {
        out.push(format!("{fqcn}::{name}: visibility"));
    }
    if theirs["static"].as_bool() != Some(ours.is_static) {
        out.push(format!("{fqcn}::{name}: static flag"));
    }
    if theirs["abstract"].as_bool() != Some(ours.is_abstract) {
        out.push(format!("{fqcn}::{name}: abstract flag"));
    }
    if theirs["ref"].as_bool() != Some(ours.returns_ref) {
        out.push(format!("{fqcn}::{name}: returns-by-ref"));
    }
    let their_ret = theirs["ret"].as_str().map(|t| canon_type_with(t, subst));
    let our_ret = ours.return_type.as_deref().map(|t| canon_type_with(t, subst));
    if our_ret != their_ret && !(magic && our_ret.is_none()) {
        out.push(format!(
            "{fqcn}::{name}: return type {our_ret:?} vs {their_ret:?}"
        ));
    }
    let their_params = theirs["params"].as_array().cloned().unwrap_or_default();
    if their_params.len() != ours.params.len() {
        out.push(format!(
            "{fqcn}::{name}: {} params vs {}",
            ours.params.len(),
            their_params.len()
        ));
        return;
    }
    for (i, (op, tp)) in ours.params.iter().zip(their_params.iter()).enumerate() {
        if tp["name"].as_str() != Some(op.name.as_str()) {
            out.push(format!(
                "{fqcn}::{name}: param {i} name {} vs {}",
                op.name,
                tp["name"].as_str().unwrap_or("?")
            ));
        }
        let their_ty = tp["type"].as_str().map(|t| canon_type_with(t, subst));
        // `Foo $x = null` is implicitly nullable — reflection reports the
        // null; the source doesn't write it.
        let implicit_null = op.default.as_deref().is_some_and(|d| d.eq_ignore_ascii_case("null"));
        let our_ty = op.ty.as_deref().map(|t| {
            if implicit_null {
                canon_type_with(&format!("{t}|null"), subst)
            } else {
                canon_type_with(t, subst)
            }
        });
        if our_ty != their_ty && !(magic && our_ty.is_none()) {
            out.push(format!(
                "{fqcn}::{name}: param ${} type {our_ty:?} vs {their_ty:?}",
                op.name
            ));
        }
        if tp["ref"].as_bool() != Some(op.by_ref) {
            out.push(format!("{fqcn}::{name}: param ${} by-ref", op.name));
        }
        if tp["variadic"].as_bool() != Some(op.variadic) {
            out.push(format!("{fqcn}::{name}: param ${} variadic", op.name));
        }
        if tp["hasDefault"].as_bool() != Some(op.default.is_some()) {
            out.push(format!("{fqcn}::{name}: param ${} default presence", op.name));
        }
        if tp["promoted"].as_bool() != Some(op.promoted.is_some()) {
            out.push(format!("{fqcn}::{name}: param ${} promotion", op.name));
        }
    }
}

/// Canonical type form matching reflect.php's `type_str`: lowercase, no
/// leading backslash, `?T` expanded to `t|null`, union members (and
/// intersection members within them) sorted, parens dropped. The `subst`
/// map resolves keywords (`self` → the class, `parent` → its parent) BEFORE
/// sorting, mirroring how PHP 8.5 reflection reports resolved names.
fn canon_type_with(ty: &str, subst: &BTreeMap<String, String>) -> String {
    let mut parts: Vec<String> = Vec::new();
    let mut nullable = false;
    for raw in split_top(ty, '|') {
        let mut p = raw.trim();
        if let Some(rest) = p.strip_prefix('?') {
            nullable = true;
            p = rest.trim();
        }
        let p = p
            .strip_prefix('(')
            .and_then(|s| s.strip_suffix(')'))
            .unwrap_or(p);
        let mut members: Vec<String> = split_top(p, '&')
            .into_iter()
            .map(|m| {
                let m = m.trim().trim_start_matches('\\').to_ascii_lowercase();
                subst.get(&m).cloned().unwrap_or(m)
            })
            .collect();
        members.sort();
        // PHP 8.5 reflection expands `iterable` to `array|Traversable`.
        if members.len() == 1 && members[0] == "iterable" {
            parts.push("array".to_owned());
            parts.push("traversable".to_owned());
        } else {
            parts.push(members.join("&"));
        }
    }
    if nullable && !parts.iter().any(|p| p == "null" || p == "mixed") {
        parts.push("null".to_owned());
    }
    // `mixed` subsumes everything (incl. the null an `= null` default implies).
    if parts.iter().any(|p| p == "mixed") {
        return "mixed".to_owned();
    }
    parts.sort();
    parts.dedup();
    parts.join("|")
}

/// Split on `sep` outside parentheses.
fn split_top(s: &str, sep: char) -> Vec<String> {
    let mut parts = Vec::new();
    let mut depth = 0usize;
    let mut cur = String::new();
    for ch in s.chars() {
        match ch {
            '(' => {
                depth += 1;
                cur.push(ch);
            }
            ')' => {
                depth = depth.saturating_sub(1);
                cur.push(ch);
            }
            c if c == sep && depth == 0 => {
                parts.push(std::mem::take(&mut cur));
            }
            _ => cur.push(ch),
        }
    }
    if !cur.is_empty() {
        parts.push(cur);
    }
    parts
}
