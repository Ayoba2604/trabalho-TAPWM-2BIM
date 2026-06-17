<?php

declare(strict_types=1);

namespace App\Core;

final class ColumnDefinition
{
    public bool $nullable = false;
    public mixed $default = null;
    public bool $hasDefault = false;

    public function __construct(
        public readonly string $name,
        public readonly string $type,
        public readonly array $options = []
    ) {
    }

    public function nullable(): self
    {
        $this->nullable = true;

        return $this;
    }

    public function default(mixed $value): self
    {
        $this->default = $value;
        $this->hasDefault = true;

        return $this;
    }
}
