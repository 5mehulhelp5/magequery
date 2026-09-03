<?php

namespace Acme\Float\Model;

class Rates
{
    /** `0.` and `.5` are both DNUM: the digits on either side are optional. */
    public function __construct(
        private float $trailing = 0.,
        private float $leading = .5,
        private float $both = 1.5,
        private float $exponent = 1.5e3,
        private float $configured = 0.0
    ) {
    }
}
