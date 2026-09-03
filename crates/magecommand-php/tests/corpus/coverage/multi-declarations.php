<?php // coverage/multi-declarations.php — several declarations in one file
// interleaved with HTML gaps and a top-level function. Covers the
// "several per file" path parse_file's dedup walk relies on, with the html
// gap between PHP blocks (declarations on both sides survive).

namespace Corp\Multi;

class One
{
    public function one(): int
    {
        return 1;
    }
}

interface Two
{
    public function two(): string;
}

trait Three
{
    public function three(): void
    {
    }
}

enum Four: string
{
    case JustFour = '4';
}

function top_level(): int
{
    return 4;
}
?>
<p>html gap</p>
<?php

class Five extends One
{
    public function five(): int
    {
        return 5;
    }
}

abstract class Six implements Two
{
    abstract public function six(): array;
}
