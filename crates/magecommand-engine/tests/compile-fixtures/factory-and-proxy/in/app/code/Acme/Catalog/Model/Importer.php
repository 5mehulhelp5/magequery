<?php

namespace Acme\Catalog\Model;

class Importer
{
    public function __construct(
        private Reader $reader,
        private RowFactory $rowFactory
    ) {
    }
}
