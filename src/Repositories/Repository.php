<?php

namespace Xgbnl\Fleet\Repositories;

use Illuminate\Database\Eloquent\Builder;

abstract class Repository extends Repositories
{
    protected array $rules = [];

    final protected function loadWith(mixed $with): Builder
    {
        $query = (clone $this->query);

        if (is_array($with) && !empty($with)) {
            return $query->with($with);
        }

        if (is_string($with)) {
            return $query->with($query);
        }

        return $query;
    }

    abstract public function values(array $columns = [], mixed $with = null): array;
}