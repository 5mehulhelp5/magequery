<?php

namespace Acme\Inherit\Model;

class FileWriter extends AbstractWriter
{
    public function target(): string
    {
        return 'file';
    }
}
