<?php

namespace Xgbnl\Fleet\Observer;

use Illuminate\Database\Eloquent\Model;
use Xgbnl\Fleet\Contacts\Observer;
use Xgbnl\Fleet\Services\BaseService;

abstract class Observable
{
    protected ?Model $model = null;

    protected ?Observer $observer = null;

    protected ?string $event = null;

    protected BaseService $service;

    final public function __construct(BaseService $service)
    {
        $this->service = $service;

        $this->configure();
    }

    private function configure(): void
    {
        if (!is_null($this->service->getObserver())) {
            $this->observer($this->service->getObserver());
        }
    }

    protected function notifyObserver(): void
    {
        if (!is_null($this->observer)) {
            $this->observer->{$this->event}($this->model);
        }
    }

    private function observer(string $observer): void
    {
        if (!is_subclass_of($observer, Observer::class)) {
            throw new \RuntimeException(
                '实例化观察者[' . $observer . ' ]发生错误，必须实现接口:[ ' . Observer::class . ' ]',
                500,
            );
        }

        $this->observer = app($observer);
    }

    final protected function triggerEvent(string $event): void
    {
        $this->event = $event;
    }

    final static public function make(BaseService $service): static
    {
        return new static($service);
    }
}