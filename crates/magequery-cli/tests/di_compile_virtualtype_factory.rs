//! `di compile` and `…Factory`-named virtual types (issue #96).
//!
//! Generation is served by the autoloader on the compile PROCESS's
//! ObjectManager, whose config holds the GLOBAL virtual types only. So the
//! scope a `…Factory` virtualType is declared in decides whether a real
//! factory class lands on disk:
//!
//! * declared in an AREA `etc/<area>/di.xml` — the process OM has never heard
//!   of the name, so `Generator::generateClass` loads its source class and
//!   writes a plain factory over it;
//! * declared in the global `etc/di.xml` — the OM knows it as a virtual type
//!   and generation is skipped;
//! * an unnamespaced, lowercase-initial name (Magento's convention for a bare
//!   virtual type) — nothing loads the source, so generation fails and writes
//!   nothing, in either scope.
//!
//! The `mg-install-310` oracle backs all three: 36 `…Factory` virtualTypes,
//! and `setup:di:compile` wrote a file for exactly the 2 declared in an
//! `etc/adminhtml/di.xml`. These fixtures need no store — they build their own
//! two-class Magento root, so they run everywhere the workspace does.

use std::path::{Path, PathBuf};
use std::process::Command;

/// A throwaway Magento root: one module, two classes, and the `di.xml` under
/// test at `rel_di` (`etc/di.xml` for global, `etc/adminhtml/di.xml` for an
/// area). Removed on drop.
struct Fixture(PathBuf);

impl Fixture {
    fn new(name: &str, rel_di: &str, di: &str) -> Self {
        let root = PathBuf::from(env!("CARGO_TARGET_TMPDIR")).join(format!("mq-vtype-{name}"));
        let _ = std::fs::remove_dir_all(&root);
        let write = |rel: &str, content: &str| {
            let path = root.join(rel);
            std::fs::create_dir_all(path.parent().unwrap()).unwrap();
            std::fs::write(path, content).unwrap();
        };
        write("app/etc/config.php", "<?php\nreturn ['modules' => ['Acme_Repro' => 1]];\n");
        write(
            "app/code/Acme/Repro/etc/module.xml",
            "<?xml version=\"1.0\"?>\n<config><module name=\"Acme_Repro\"/></config>\n",
        );
        // The factory's source class: the virtualType name minus `Factory`.
        write(
            "app/code/Acme/Repro/Model/Foo.php",
            "<?php\nnamespace Acme\\Repro\\Model;\n\nclass Foo\n{\n}\n",
        );
        // The virtualType's `type=`. The generator never consults it — the
        // file it writes is a factory over the NAME minus `Factory`.
        write(
            "app/code/Acme/Repro/Model/Builder.php",
            "<?php\nnamespace Acme\\Repro\\Model;\n\nclass Builder\n{\n}\n",
        );
        write(rel_di, di);
        Fixture(root)
    }

    fn compile(&self) {
        let out = Command::new(env!("CARGO_BIN_EXE_magecommand"))
            .args(["di", "compile", "--root"])
            .arg(&self.0)
            .output()
            .expect("run magecommand");
        assert!(
            out.status.success(),
            "di compile failed: {}",
            String::from_utf8_lossy(&out.stderr)
        );
    }

    fn generated(&self, rel: &str) -> PathBuf {
        self.0.join("generated/code").join(rel)
    }
}

impl Drop for Fixture {
    fn drop(&mut self) {
        let _ = std::fs::remove_dir_all(&self.0);
    }
}

const AREA_DI: &str = r#"<?xml version="1.0"?>
<config>
    <virtualType name="Acme\Repro\Model\FooFactory" type="Acme\Repro\Model\Builder"/>
</config>
"#;

fn read(path: &Path) -> String {
    std::fs::read_to_string(path).unwrap_or_else(|e| panic!("read {}: {e}", path.display()))
}

/// The issue-#96 case: an area-scoped `…Factory` virtualType still gets a real
/// factory class, over the source the NAME names — not over the `type=`.
#[test]
fn area_scoped_factory_virtualtype_is_generated() {
    let fx = Fixture::new("area", "app/code/Acme/Repro/etc/adminhtml/di.xml", AREA_DI);
    fx.compile();

    let factory = fx.generated("Acme/Repro/Model/FooFactory.php");
    assert!(
        factory.is_file(),
        "an adminhtml-scoped `…Factory` virtualType must still generate its factory class"
    );
    let src = read(&factory);
    assert!(
        src.contains("Factory class for @see \\Acme\\Repro\\Model\\Foo\n"),
        "factory must be built over the name minus `Factory`, not over the virtualType's \
         type=; got:\n{src}"
    );
    assert!(src.contains("class FooFactory\n"), "got:\n{src}");
}

/// The control: the same declaration in the GLOBAL di.xml is a virtual type in
/// the process OM's own config, so the generator skips it.
#[test]
fn global_factory_virtualtype_is_not_generated() {
    let fx = Fixture::new("global", "app/code/Acme/Repro/etc/di.xml", AREA_DI);
    fx.compile();
    assert!(
        !fx.generated("Acme/Repro/Model/FooFactory.php").exists(),
        "a global `…Factory` virtualType is known to the OM and must not be generated"
    );
}

/// An unnamespaced virtual type — Magento writes these lowercase-initial, and
/// PHP has no such built-in class, so its source never loads and no file is
/// written. Guards the area sweep against emitting junk for names like the
/// graphql `amPromoQuoteItemFactory`.
#[test]
fn bare_lowercase_factory_virtualtype_is_not_generated() {
    let di = r#"<?xml version="1.0"?>
<config>
    <virtualType name="acmeReproItemFactory" type="Acme\Repro\Model\Builder"/>
</config>
"#;
    let fx = Fixture::new("bare", "app/code/Acme/Repro/etc/graphql/di.xml", di);
    fx.compile();
    assert!(
        !fx.generated("acmeReproItemFactory.php").exists(),
        "`acmeReproItem` is not a loadable class, so its factory must not be generated"
    );
}
