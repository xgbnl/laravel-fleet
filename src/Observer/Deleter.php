<?php

namespace Xgbnl\Fleet\Observer;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Xgbnl\Fleet\Enum\Trigger;

class Deleter extends Observable
{
    public function delete(int $value, string $by = 'id'): bool
    {
        $this->model = $this->service->query->where($by, $value)->first();

        if (is_null($this->model)) {
            throw new \RuntimeException('模型不存在,删除操作无效', 500);
        }

        try {
            $this->model->delete();

            $this->triggerEvent(Trigger::Deleted);
        } catch (Exception $e) {
            $error = class_basename($this) . '::destroy' . '删除数据失败:[ ' . $e->getMessage() . ' ]';
            Log::error($error);
            throw new \RuntimeException($error, 500, $e);
        }

        $this->notifyObserver();

        return true;
    }

    public function batchDelete(array $values, string $by = 'id'): int
    {
        $count = 0;

        foreach ($this->service->query->whereIn($by, $values)->get() as $model) {
            if ($model->delete()) {
                $count++;
            }
        }

        return $count;
    }
}