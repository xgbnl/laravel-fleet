<?php

namespace Xgbnl\Fleet\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

abstract class Repository extends Repositories
{
    protected array $rules = [];

    final public function find(
        mixed   $value,
        array   $columns = ['*'],
        string  $by = 'id',
        mixed   $with = [],
        bool    $transform = false,
        ?string $replaceCall = null,
    ): array|Model|null
    {
        $builder = $this->loadWith($with);

        $model = $by === 'id'
            ? $builder->find($value, $columns)
            : $builder->select($columns)->where($by, $value)->first();

        if (is_null($model)) {
            return null;
        }

        if ($this->transform && $transform) {
            return is_null($replaceCall) ? $this->transform->transformers($model) : $replaceCall($model);
        }

        return $model;
    }

    final public function values(
        array   $columns = ['*'],
        array   $params = [],
        mixed   $with = null,
        bool    $transform = false,
        bool    $chunk = false,
        int     $count = 200,
        ?string $replaceCall = null,
    ): array
    {
        $builder = $this->loadWith($with);

        if (!empty($params)) {
            $builder = $this->query($params, $builder);
        }

        if ($chunk) {
            return $this->chunk($columns, $count, $builder, $replaceCall);
        }

        if ($this->transform && $transform) {
            $list = [];

            $builder->select($columns)->each(function (Model $model) use ($replaceCall, &$list) {
                $list[] = is_null($replaceCall)
                    ? $this->transform->transformers($model)
                    : $this->transform->{$replaceCall}($model);
            });

            return $list;
        }

        return $builder->select($columns)->get()->toArray();
    }

    private function chunk(array $columns, int $count, Builder $builder, ?string $replaceCall): array
    {
        if ($builder->count() <= 0) {
            return [];
        }

        $list = [];
        $builder->select($columns)->chunkById($count, function (Collection $collection) use (&$list, $replaceCall) {
            $collection->each(function (Model $model) use (&$list, $replaceCall) {
                $list [] = (is_null($this->transform) ? $model : is_null($replaceCall))
                    ? $this->transform->transformers($model) : $replaceCall($model);
            });
        });

        return $list;
    }

    final protected function query(array $params, Builder $builder): Builder
    {
        if (count($params) === 1) {
            $keys = array_keys($params);
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
