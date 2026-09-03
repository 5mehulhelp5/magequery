<?php
// coverage/no-declarations.php — valid PHP the scan must swallow silently:
// a registration-style file with calls, an eval'd-looking bootstrap and no
// type declarations. Pins the zero-declaration, zero-issue outcome.

use Magento\Framework\Component\ComponentRegistrar;

ComponentRegistrar::register(
    ComponentRegistrar::MODULE,
    'Corp_NoDeclarations',
    __DIR__
);

if (function_exists('corp_bootstrap')) {
    corp_bootstrap(__DIR__);
}
