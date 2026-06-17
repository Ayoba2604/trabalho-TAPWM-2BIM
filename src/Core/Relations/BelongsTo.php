<?php

declare(strict_types=1);

namespace App\Core\Relations;

use App\Core\Model;

final class BelongsTo implements Relation
{
    public function __construct(
        private readonly Model $child,
        private readonly string $relatedClass,
        private readonly string $foreignKey,
        private readonly string $ownerKey
    ) {
    }

    public function getResults(): ?Model
    {
        return $this->relatedClass::where($this->ownerKey, $this->child->getAttribute($this->foreignKey))->first();
    }

    public function eagerLoad(array $models, string $relationName): void
    {
        $keys = array_values(array_unique(array_filter(array_map(
            fn (Model $model): mixed => $model->getAttribute($this->foreignKey),
            $models
        ))));

        $relatedModels = $this->relatedClass::query()
            ->whereIn($this->ownerKey, $keys)
            ->get();

        $indexed = [];
        foreach ($relatedModels as $relatedModel) {
            $indexed[$relatedModel->getAttribute($this->ownerKey)] = $relatedModel;
        }

        foreach ($models as $model) {
            $model->setRelation($relationName, $indexed[$model->getAttribute($this->foreignKey)] ?? null);
        }
    }
}
