<?php

namespace Acme\Iface\Model;

use Acme\Iface\Api\SourceInterface;

class Impl extends Base implements SourceInterface
{
    public function pull(): array
    {
        return $this->items;
    }
}
