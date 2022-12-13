<?php

namespace Xgbnl\Fleet\Traits;

use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Xgbnl\Fleet\Enum\Sign;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

/**
 * @property-read Model $model
 * @property-read string|null $table
 * @property-read EloquentBuilder $query
 */
trait BuilderGenerator
{
    private ?string $modelName = null;
    private ?string $tableName = null;

    final public function __get(string $name)
    {
        return match ($name) {
            'table' => $this->getTable(),
            'model' => $this->makeModel(),
            'query' => $this->makeModel(instance: false)::query(),
            default => $this->dynamicGet($name),
        };
    }

    private function getTable(): string
    {
        return is_null($this->tableName) ? $this->tableName = $this->makeModel()->getTable() : $this->tableName;
    }

    private function getModel(): string
    {
        if (!is_null($this->modelName)) {
            return $this->modelName;
        }

        $baseName = strEndWith(
            last(explode('\\', get_called_class())),
            [ucwords(Sign::Service), ucwords(Sign::Repository)]
        );

        $clazz = 'App\\Models\\' . $baseName;

        if (!class_exists($clazz)) {
            throw new \RuntimeException('缺少模型 [ ' . $baseName . ' ]');
        }
        return $this->modelName = $clazz;
    }

    private function makeModel(bool $instance = true): Model|string
    {
        $class = $this->getModel();

        if (!is_subclass_of($class, Model::class)) {
            $msg = '模型文件 [ ' . $class . ' ]错误,必须继承 [ ' . Model::class . ' ]';
            Log::error($msg);
            throw new \RuntimeException($msg);
        }

        if (!$instance) {
            return $class;
        }

        try {
            $builder = app($class);
        } catch (BindingResolutionException $e) {
            throw new \RuntimeException('模型文件实例化时错误', previous: $e->getTrace());
        } catch (Exception $e) {
            throw new \RuntimeException($e->getMessage());
        }

        return $builder;
    }
}
