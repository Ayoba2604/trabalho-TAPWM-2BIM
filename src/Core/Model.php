<?php

declare(strict_types=1);

namespace App\Core;

use App\Core\Relations\BelongsTo;
use App\Core\Relations\HasMany;
use App\Core\Relations\Relation;

abstract class Model
{
    protected string $table;
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $attributes = [];
    protected array $relations = [];

    public function __construct(array $attributes = [])
    {
        $this->attributes = $attributes;
    }

    public static function query(): QueryBuilder
    {
        return new QueryBuilder(new static());
    }

    public static function all(): array
    {
        return static::query()->get();
    }

    public static function with(string|array $relations): QueryBuilder
    {
        return static::query()->with($relations);
    }

    public static function where(string $column, mixed $operator, mixed $value = null): QueryBuilder
    {
        return static::query()->where($column, $operator, $value);
    }

    public static function create(array $attributes): static
    {
        $model = new static();
        $allowed = array_intersect_key($attributes, array_flip($model->fillable));

        $columns = array_keys($allowed);
        $placeholders = array_map(fn (string $column): string => ':' . $column, $columns);

        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            Database::quoteIdentifier($model->getTable()),
            implode(', ', array_map([Database::class, 'quoteIdentifier'], $columns)),
            implode(', ', $placeholders)
        );

        Database::statement($sql, $allowed);
        $allowed[$model->primaryKey] = (int) Database::pdo()->lastInsertId();

        return new static($allowed);
    }

    public function __get(string $key): mixed
    {
        if (array_key_exists($key, $this->relations)) {
            return $this->relations[$key];
        }

        if (array_key_exists($key, $this->attributes)) {
            return $this->attributes[$key];
        }

        if (method_exists($this, $key)) {
            $relation = $this->{$key}();

            if ($relation instanceof Relation) {
                return $this->relations[$key] = $relation->getResults();
            }
        }

        return null;
    }

    public function __isset(string $key): bool
    {
        return isset($this->attributes[$key]) || isset($this->relations[$key]);
    }

    public function getTable(): string
    {
        return $this->table;
    }

    public function getPrimaryKey(): string
    {
        return $this->primaryKey;
    }

    public function getKey(): mixed
    {
        return $this->attributes[$this->primaryKey] ?? null;
    }

    public function getAttribute(string $key): mixed
    {
        return $this->attributes[$key] ?? null;
    }

    public function setRelation(string $name, mixed $value): void
    {
        $this->relations[$name] = $value;
    }

    public function newFromAttributes(array $attributes): static
    {
        return new static($attributes);
    }

    protected function hasMany(string $relatedClass, string $foreignKey, ?string $localKey = null): HasMany
    {
        return new HasMany($this, $relatedClass, $foreignKey, $localKey ?? $this->primaryKey);
    }

    protected function belongsTo(string $relatedClass, string $foreignKey, string $ownerKey = 'id'): BelongsTo
    {
        return new BelongsTo($this, $relatedClass, $foreignKey, $ownerKey);
    }
}
