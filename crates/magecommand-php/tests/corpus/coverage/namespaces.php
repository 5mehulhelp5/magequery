<?php
// coverage/namespaces.php — statement + braced namespaces, multiple
// declarations per file and per namespace, a file-global (unnamespaced)
// declaration, and a nested namespace. The manifest pins the exact
// declaration set and their resolved FQCNs.

namespace Corp\Namespaced\Statement;

class StatementNsClass {}

interface StatementNsIfc {}

trait StatementNsTrait {}

namespace Corp\Namespaced\Braced
{
    class BracedNsClass
    {
        public function create(): StatementNsIfc
        {
            return new StatementNsClass();
        }
    }

    class BracedSecond {}

    enum BracedEnum: int
    {
        case Low = 0;
        case High = 1;
    }
}

namespace Corp\Namespaced\Sub\Deep
{
    class DeepClass {}
}

class GlobalNsClass {}

function global_helper(): void
{
}
