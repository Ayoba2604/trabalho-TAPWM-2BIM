<?php

declare(strict_types=1);

namespace App\Core;

final class ForeignKeyDefinition
{
    public string $references = 'id';
    public string $on = '';
    public bool $cascadeOnDelete = false;

    public function __construct(public readonly string $column)
    {
    }

    public function references(string $column): self
    {
        $this->references = $column;

        return $this;
    }

    public function on(string $table): self
    {
        $this->on = $table;

        return $this;
    }

    public function cascadeOnDelete(): self
    {
        $this->cascadeOnDelete = true;

        return $this;
    }
}
