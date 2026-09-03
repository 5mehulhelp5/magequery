<?php

namespace Acme\Shop\Api;

interface CheckoutInterface
{
    public function place(string $cartId, ?int $retries = null): bool;
}
