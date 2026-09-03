<?php

namespace Acme\Shop\Plugin;

use Acme\Shop\Model\Checkout;

class Audit
{
    public function beforePlace(Checkout $subject, string $cartId, ?int $retries = null): array
    {
        return [$cartId, $retries];
    }

    public function afterPlace(Checkout $subject, bool $result): bool
    {
        return $result;
    }
}
