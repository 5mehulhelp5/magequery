<?php

namespace Acme\Shop\Model;

use Acme\Shop\Api\CheckoutInterface;

class Checkout implements CheckoutInterface
{
    public function __construct(
        private string $label = 'default',
        private int $retries = 1,
        private array $flags = []
    ) {
    }

    public function place(string $cartId, ?int $retries = null): bool
    {
        return true;
    }
}
