<?php

declare(strict_types=1);

namespace App\Core\Relations;

use App\Core\Model;

final class HasMany implements Relation
{
    public function __construct(
        private readonly Model $parent,
        private readonly string $relatedClass,
        private readonly string $foreignKey,
        private readonly string $localKey
    ) {
    }

    public function getResults(): array
    {
        return $this->relatedClass::where($this->foreignKey, $this->parent->getAttribute($this->localKey))
            ->orderBy('id')
            ->get();
    }

    public function eagerLoad(array $models, string $relationName): void
    {
        $keys = array_values(array_unique(array_filter(array_map(
            fn (Model $model): mixed => $model->getAttribute($this->localKey),
            $models
        ))));

        $relatedModels = $this->relatedClass::query()
            ->whereIn($this->foreignKey, $keys)
            ->orderBy('id')
            ->get();

        $grouped = [];
        foreach ($relatedModels as $relatedModel) {
            $grouped[$relatedModel->getAttribute($this->foreignKey)][] = $relatedModel;
        }

        foreach ($models as $model) {
            $model->setRelation($relationName, $grouped[$model->getAttribute($this->localKey)] ?? []);
        }
    }
}
