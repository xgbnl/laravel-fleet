<?php

namespace Xgbnl\Fleet\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class Repository extends Repositories
{
    protected array $rules = [];

    private array $queryRules = [
        'like' => '%?%',
        'suffix_like' => '%?',
        'prefix_like' => '?%',
        'date',
        'in',
        'not_in',
    ];

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
        foreach ($params as $column => $value) {
            if (isset($this->rules[$column])) {
                $builder = match ($this->rules[$column]) {
                    'like', 'suffix_like', 'prefix_like' => $builder->where(
                        $this->rules[$column],
                        $this->splice($this->queryRules[$this->rules[$column]], $value),
                    ),
                    'date' => $builder->whereDate($column, '>=', $value)
                        ->orWhereDate($column, '<=', $value),
                    'in' => $builder->whereIn($column, $value),
                    'not_in' => $builder->whereNotIn($column, $value),
                };
                continue;
            }
            $builder = $builder->where($column, $value);
        }

        return $builder;
    }

    private function splice(string $rule, mixed $value): string
    {
        $split = str_split($rule);

        $index = array_search('?', $split);

        array_splice($split, $index, 1, $value);

        return implode('', $split);
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