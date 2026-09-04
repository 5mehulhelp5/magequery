<?php

namespace Acme\Neverr\Model;

/**
 * A plugged `never`-returning method.
 *
 * NOT ORACLE-VERIFIABLE, on purpose. Magento's OWN generator emits
 *
 *     public function boom(): never { ... return $pluginInfo ? ... ; }
 *
 * and PHP rejects it at include time ("A never-returning method must not
 * return"), which aborts `setup:di:compile` outright. magecommand reproduces
 * those bytes exactly — verified by diffing against the file Magento wrote
 * before it died — so this fixture pins FAITHFULNESS TO A MAGENTO BUG, which
 * is what a byte-exact reimplementation owes. Installing it in a real store
 * breaks the compile there too; that is the upstream bug, not ours.
 */
class Hard
{
    public function boom(): never
    {
        throw new \RuntimeException('x');
    }
}
