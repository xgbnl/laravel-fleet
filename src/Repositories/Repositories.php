<?php

declare(strict_types=1);

namespace Xgbnl\Fleet\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Xgbnl\Fleet\Enum\Sign;
use Xgbnl\Fleet\Traits\CallMethodCollection;
use Xgbnl\Fleet\Contacts\Transform;
use Xgbnl\Fleet\Traits\BuilderGenerator;
use Illuminate\Contracts\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Facades\DB;

/**
 * @property-read QueryBuilder $rawQuery
 * @property-read Transform|null $transform
 */
abstract class Repositories
{
    use CallMethodCollection, BuilderGenerator;

    private ?string $transformModel = null;

    protected function dynamicGet(string $name): QueryBuilder|Transform|null
    {
        return match ($name) {
            'rawQuery' => $this->table
                ? DB::table($this->table)
                : throw new \RuntimeException('获取数据表:[ ' . $this->table . ' ]错误', 500),
            'transform' => $this->getTransform(),
        };
    }

    private function getTransform(): ?Transform
    {
        if (!is_null($this->transformModel)) {
            return app($this->transformModel);
        }

        $name = strEndWith(last(explode('\\', get_called_class())), ucwords(Sign::Repository));

        $clazz = 'App\\Transforms\\' . $name . ucwords(Sign::Transform);

        if (!class_exists($clazz)) {
            return null;
        }

        if (!is_subclass_of($clazz, Transform::class)) {
            $msg = '转换层模型[ ' . $clazz . ' ]必须继承[ ' . Transform::class . ' ]';
            Log::error($msg);
            throw new \RuntimeException($msg, 500);
        }

        $this->transformModel = $clazz;

        try {
            $class = app($clazz);
        } catch (\Exception $e) {
            throw new \RuntimeException('获取[ ' . $clazz . ' ]实例时出错' . $e->getMessage(), 500);
        }

        return $class;
    }
}
