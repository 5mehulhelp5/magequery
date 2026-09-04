<?php

namespace Acme\Gen\Model;

/**
 * The generated-class surface reached by NAME: a Proxy, and (via
 * extension_attributes.xml) the Extension / ExtensionInterface /
 * ExtensionInterfaceFactory triple.
 *
 * Deliberately NOT referenced here: a name whose kind has no emitter
 * (`...Repository`, `...Logger`, …), `...SearchResults`, or
 * `...\ProxyDeferred`. magecommand records those in `unresolved` and carries
 * on, but real `setup:di:compile` treats them as FATAL —
 * `Class "Acme\Gen\Model\HeavyRepository" does not exist` aborts the whole
 * run. Keeping them here would make this fixture impossible to ground-truth
 * against Magento, which matters more than pinning a diagnostic.
 */
class Uses
{
    public function __construct(
        private \Acme\Gen\Model\Heavy\Proxy $proxy,
        private \Acme\Gen\Api\Data\ItemExtensionInterfaceFactory $extFactory
    ) {
    }
}
