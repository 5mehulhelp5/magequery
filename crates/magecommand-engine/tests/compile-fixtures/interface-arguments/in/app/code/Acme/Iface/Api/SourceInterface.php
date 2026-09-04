<?php

namespace Acme\Iface\Api;

interface SourceInterface
{
    public function pull(): array;
}
