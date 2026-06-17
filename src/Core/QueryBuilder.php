<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Relations\Relation;
use PDO;

final class QueryBuilder
{
    private array $wheres = [];
    private array $orders = [];
    private array $eagerLoads = [];

    public function __construct(private readonly Model $model)
    {
    }

    public function where(string $column, mixed $operator, mixed $value = null): self
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }

        $this->wheres[] = [
            'type' => 'basic',
            'column' => $column,
            'operator' => $operator,
            'value' => $value,
        ];

        return $this;
    }

    public function whereIn(string $column, array $values): self
    {
        $this->wheres[] = [
            'type' => 'in',
            'column' => $column,
            'values' => array_values($values),
        ];

        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $direction = strtolower($direction) === 'desc' ? 'DESC' : 'ASC';
        $this->orders[] = [$column, $direction];

        return $this;
    }

    public function with(string|array $relations): self
    {
        foreach ((array) $relations as $relation) {
            $this->eagerLoads[] = $relation;
        }

        return $this;
    }

    public function get(): array
    {
        [$sql, $bindings] = $this->toSql();
        $statement = Database::pdo()->prepare($sql);
        $statement->execute($bindings);

        $models = array_map(
            fn (array $row): Model => $this->model->newFromAttributes($row),
            $statement->fetchAll(PDO::FETCH_ASSOC)
        );

        foreach ($this->eagerLoads as $relationName) {
            $this->eagerLoad($models, $relationName);
        }

        return $models;
    }

    public function first(): ?Model
    {
        [$sql, $bindings] = $this->toSql(1);
        $statement = Database::pdo()->prepare($sql);
        $statement->execute($bindings);
        $row = $statement->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->model->newFromAttributes($row) : null;
    }

    public function delete(): int
    {
        [$whereSql, $bindings] = $this->compileWhere();
        $sql = sprintf('DELETE FROM %s%s', Database::quoteIdentifier($this->model->getTable()), $whereSql);
        $statement = Database::pdo()->prepare($sql);
        $statement->execute($bindings);

        return $statement->rowCount();
    }

    private function toSql(?int $limit = null): array
    {
        [$whereSql, $bindings] = $this->compileWhere();
        $sql = sprintf('SELECT * FROM %s%s', Database::quoteIdentifier($this->model->getTable()), $whereSql);

        if ($this->orders !== []) {
            $orders = array_map(
                fn (array $order): string => Database::quoteIdentifier($order[0]) . ' ' . $order[1],
                $this->orders
            );
            $sql .= ' ORDER BY ' . implode(', ', $orders);
        }

        if ($limit !== null) {
            $sql .= ' LIMIT ' . $limit;
        }

        return [$sql, $bindings];
    }

    private function compileWhere(): array
    {
        if ($this->wheres === []) {
            return ['', []];
        }

        $parts = [];
        $bindings = [];

        foreach ($this->wheres as $index => $where) {
            $column = Database::quoteIdentifier($where['column']);

            if ($where['type'] === 'in') {
                if ($where['values'] === []) {
                    $parts[] = '1 = 0';
                    continue;
                }

                $placeholders = [];
                foreach ($where['values'] as $valueIndex => $value) {
                    $key = ":w{$index}_{$valueIndex}";
                    $placeholders[] = $key;
                    $bindings[$key] = $value;
                }

                $parts[] = $column . ' IN (' . implode(', ', $placeholders) . ')';
                continue;
            }

            $key = ':w' . $index;
            $parts[] = "{$column} {$where['operator']} {$key}";
            $bindings[$key] = $where['value'];
        }

        return [' WHERE ' . implode(' AND ', $parts), $bindings];
    }

    private function eagerLoad(array $models, string $relationName): void
    {
        if ($models === [] || !method_exists($models[0], $relationName)) {
            return;
        }

        $relation = $models[0]->{$relationName}();

        if (!$relation instanceof Relation) {
            return;
        }

        $relation->eagerLoad($models, $relationName);
    }
}
