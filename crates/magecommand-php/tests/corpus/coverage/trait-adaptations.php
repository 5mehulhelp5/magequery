<?php
// coverage/trait-adaptations.php — the trait surface: multi-trait use with
// insteadof conflict resolution, as with visibility, as with alias, as with
// both, unqualified method references, abstract trait methods, and trait
// members of every kind.

namespace Corp\Traits;

trait Speaks
{
    public function hello(): string
    {
        return 'speaks';
    }

    public function volume(): int
    {
        return 1;
    }
}

trait Whispers
{
    public function hello(): string
    {
        return 'whispers';
    }

    public function volume(): int
    {
        return 2;
    }

    abstract protected function secret(): string;
}

trait WithConstants
{
    public const FROM_TRAIT = 'c';

    protected function internal(): void
    {
    }
}

class Animal
{
    use Speaks, Whispers {
        Speaks::hello insteadof Whispers;
        Whispers::volume insteadof Speaks;
        Speaks::volume as quiet;
        Whispers::volume as protected whisperVolume;
        Speaks::hello as loud;
        hello as greet;
    }

    public function speak(): string
    {
        return $this->greet();
    }
}

abstract class PartialAnimal
{
    use WithConstants;

    protected function secret(): string
    {
        return 'hidden';
    }
}

trait Solo
{
    public function solo(): static
    {
        return $this;
    }
}

class UsesSolo
{
    use Solo;
}
