<?php

declare(strict_types=1);

namespace App\Core;

final class Schema
{
    public function create(string $table, callable $callback): void
    {
        $blueprint = new Blueprint();
        $callback($blueprint);

        Database::statement($blueprint->toSql($table));
    }

    public function dropIfExists(string $table): void
    {
        Database::statement('DROP TABLE IF EXISTS ' . Database::quoteIdentifier($table));
    }
}
