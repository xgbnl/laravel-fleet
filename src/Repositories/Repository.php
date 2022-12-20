<?php

namespace Xgbnl\Fleet\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class Repository extends Repositories
{
    protected array $rules = [];

    final public function find(mixed $value, array $columns = ['*'], string $by = 'id', mixed $with = []): array|Model|null
    {
        $builder = $this->loadWith($with);

        $model = $by === 'id'
            ? $builder->find($value, $columns)
            : $builder->select($columns)->where($by, $value)->first();

        return !is_null($model) ? $this->transform ? $this->transform->transformers($model) : $model : null;
    }

    final public function values(array $columns = ['*'], array $params = [], mixed $with = null, bool $chunk = false, int $count = 200): array
    {
        $builder = $this->loadWith($with);

        if (!empty($params)) {
            $builder = $this->query($params, $builder);
        }

        if ($chunk) {
            return $this->chunk($columns, $count, $builder);
        }

        if ($this->transform) {
            $list = [];

            $builder->select($columns)->each(function (Model $model) use (&$list) {
                $list[] = $this->transform->transformers($model);
            });

            return $list;
        }

        return $builder->select($columns)->get()->toArray();
    }

    private function chunk(array $columns, int $count, Builder $builder): array
    {
        if ($builder->count() <= 0) {
            return [];
        }

        $list = [];
        $builder->select($columns)->chunkById($count, function (Collection $collection) use (&$list) {
            $collection->each(function (Model $model) use (&$list) {
                $list [] = !$this->transform ? $model : $this->transform->transformers($model);
            });
        });

        return $list;
    }

    final protected function query(array $params, Builder $builder): Builder
    {
        if (count($params) === 1) {
            $keys   = array_keys($params);
            $column = array_pop($keys);

            return (isset($this->rules[$column]) && (is_string($this->rules[$column]) && !empty($this->rules[$column])))
                ? $this->matchQuery($column, $params[$column], $this->rules[$column], $builder)
                : $builder->where($params);
        }

        foreach ($params as $column => $value) {

            if (isset($this->rules[$column])) {
                $builder = $this->matchQuery($column, $value, $this->rules[$column], $builder);
                continue;
            }
            $builder = $builder->where($column, $value);
        }
        unset($column, $value);

        return $builder;
    }

    private function matchQuery(string $column, string $value, string $rule, Builder $builder): Builder
    {
        return match ($rule) {
            'like'  => $builder->where($column, $rule, '%' . $value . '%'),
            'date'  => $builder->whereDate($column, '>=', $value)
                ->orWhereDate($column, '<=', $value),
            'in'    => $builder->whereIn($column, $value),
            'notin' => $builder->whereNotIn($column, $value),
        };
    }

    private function loadWith(mixed $with): Builder
    {
        $query = $this->query->clone();

        if ((is_array($with) && !empty($with)) || is_string($with)) {
            return $query->with($with);
        }

        return $query;
    }
}
