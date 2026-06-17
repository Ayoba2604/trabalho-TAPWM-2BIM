<?php

declare(strict_types=1);

namespace App\Core\Relations;

interface Relation
{
    public function getResults(): mixed;

    public function eagerLoad(array $models, string $relationName): void;
}
