<?php

namespace Xgbnl\Fleet\Observer;

use Xgbnl\Fleet\Enum\Trigger;
use Xgbnl\Fleet\Utils\Fail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\{DB, Log};

class Updater extends Observable
{
    public function update(array $data, array $attributes): Model
    {
        $this->model = $this->service->query->updateOrCreate($attributes, $data);

        $this->triggerEvent(Trigger::Updated);

        $this->notifyObserver();

        return $this->model;
    }

    public function transactionUpdate(array $data, array $attributes): Model
    {
        try {
            DB::beginTransaction();
            $this->model = $this->service->query->updateOrCreate($attributes, $data);
            DB::commit();

            $this->triggerEvent(Trigger::Updated);
        } catch (Throwable $e) {
            DB::rollBack();
            $msg = '更新数据错误 [ ' . $e->getMessage() . ' ]';
            Log::error($msg);
            throw new \RuntimeException($msg, 500, $e);
        }

        $this->notifyObserver();

        return $this->model;
    }
}