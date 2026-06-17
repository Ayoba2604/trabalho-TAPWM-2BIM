<?php

declare(strict_types=1);

namespace App\Core;

final class Blueprint
{
    /** @var ColumnDefinition[] */
    private array $columns = [];

    /** @var ForeignKeyDefinition[] */
    private array $foreignKeys = [];

    public function id(string $name = 'id'): ColumnDefinition
    {
        return $this->addColumn($name, 'id');
    }

    public function foreignId(string $name): ColumnDefinition
    {
        return $this->addColumn($name, 'foreignId');
    }

    public function string(string $name, int $length = 255): ColumnDefinition
    {
        return $this->addColumn($name, 'string', ['length' => $length]);
    }

    public function text(string $name): ColumnDefinition
    {
        return $this->addColumn($name, 'text');
    }

    public function date(string $name): ColumnDefinition
    {
        return $this->addColumn($name, 'date');
    }

    public function enum(string $name, array $values): ColumnDefinition
    {
        return $this->addColumn($name, 'enum', ['values' => $values]);
    }

    public function timestamps(): void
    {
        $this->addColumn('created_at', 'timestamp')->nullable();
        $this->addColumn('updated_at', 'timestamp')->nullable();
    }

    public function foreign(string $column): ForeignKeyDefinition
    {
        $foreignKey = new ForeignKeyDefinition($column);
        $this->foreignKeys[] = $foreignKey;

        return $foreignKey;
    }

    public function toSql(string $table): string
    {
        $definitions = array_map(
            fn (ColumnDefinition $column): string => $this->compileColumn($column),
            $this->columns
        );

        foreach ($this->foreignKeys as $foreignKey) {
            $definitions[] = sprintf(
                'FOREIGN KEY (%s) REFERENCES %s(%s)%s',
                Database::quoteIdentifier($foreignKey->column),
                Database::quoteIdentifier($foreignKey->on),
                Database::quoteIdentifier($foreignKey->references),
                $foreignKey->cascadeOnDelete ? ' ON DELETE CASCADE' : ''
            );
        }

        return sprintf(
            'CREATE TABLE IF NOT EXISTS %s (%s)',
            Database::quoteIdentifier($table),
            implode(', ', $definitions)
        );
    }

    private function addColumn(string $name, string $type, array $options = []): ColumnDefinition
    {
        $column = new ColumnDefinition($name, $type, $options);
        $this->columns[] = $column;

        return $column;
    }

    private function compileColumn(ColumnDefinition $column): string
    {
        $name = Database::quoteIdentifier($column->name);
        $driver = Database::driver();

        $type = match ($column->type) {
            'id' => $driver === 'mysql' ? 'BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY' : 'INTEGER PRIMARY KEY AUTOINCREMENT',
            'foreignId' => $driver === 'mysql' ? 'BIGINT UNSIGNED NOT NULL' : 'INTEGER NOT NULL',
            'string' => 'VARCHAR(' . $column->options['length'] . ')',
            'text' => 'TEXT',
            'date' => 'DATE',
            'enum' => $driver === 'mysql'
                ? "ENUM('" . implode("', '", $column->options['values']) . "')"
                : 'VARCHAR(30) CHECK (' . $name . " IN ('" . implode("', '", $column->options['values']) . "'))",
            'timestamp' => $driver === 'mysql' ? 'TIMESTAMP NULL' : 'DATETIME NULL',
            default => $column->type,
        };

        $definition = "{$name} {$type}";

        if ($column->nullable && !str_contains($type, 'NULL')) {
            $definition .= ' NULL';
        } elseif (!$column->nullable && !str_contains($type, 'PRIMARY KEY') && !str_contains($type, 'NOT NULL') && !str_contains($type, 'NULL')) {
            $definition .= ' NOT NULL';
        }

        if ($column->hasDefault) {
            $definition .= ' DEFAULT ' . Database::pdo()->quote((string) $column->default);
        }

        return $definition;
    }
}
