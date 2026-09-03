<?php
// coverage/classes-modifiers.php — the declaration modifier matrix:
// abstract/final/readonly classes, extends + implements lists, interfaces
// extending several interfaces, traits, enums (backed + plain, with
// constants, methods, cases, implements), abstract and final methods.

namespace Corp\Modifiers;

interface BaseIfc {}

interface ExtraIfc {}

trait SharedBehavior
{
    public function shared(): string
    {
        return 'shared';
    }

    abstract protected function mustImplement(): int;
}

abstract class AbstractBase implements BaseIfc
{
    abstract public function abstractMethod(string $x): bool;

    public function concrete(): void
    {
    }
}

final class FinalChild extends AbstractBase implements ExtraIfc
{
    public function abstractMethod(string $x): bool
    {
        return $x !== '';
    }
}

readonly class ReadonlyValue
{
    public function __construct(
        public readonly string $name,
        private readonly int $count = 0,
    ) {
    }
}

trait OnlyTrait
{
    public function traitMethod(): void
    {
    }
}

interface MultiExtends extends BaseIfc, ExtraIfc, \Corp\Namespaced\Statement\StatementNsIfc
{
    public function multi(): string;
}

enum IntBacked: int implements BaseIfc
{
    case First = 1;
    case Second = 2;

    public const FALLBACK = self::First;

    public function label(): string
    {
        return match ($this) {
            self::First => 'first',
            self::Second => 'second',
        };
    }
}

enum PlainEnum
{
    case Alpha;
    case Beta;

    public static function fromName(string $name): self
    {
        return constant("self::$name");
    }
}

enum StringBacked: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
