<?php

namespace Xgbnl\Fleet\Repositories;

use Illuminate\Database\Eloquent\Builder;

abstract class Repository extends Repositories
{
    protected array $rules = [];

    final protected function loadWith(mixed $with): Builder
    {
        $query = $this->query->clone();

        if ((is_array($with) && !empty($with)) || is_string($with)) {
            return $query->with($with);
        }

        return $query;
    }

    abstract public function values(array $columns = [], mixed $with = null, bool $chunk = false, int $count = 200): array;
}