<?php

namespace Xgbnl\Fleet\Traits;

use Illuminate\Foundation\Http\FormRequest;
use Xgbnl\Fleet\Cache\Cacheable;
use Xgbnl\Fleet\Enum\Sign;
use Xgbnl\Fleet\Repositories\Repositories;
use Xgbnl\Fleet\Services\BaseService;

/**
 * @property Repositories $repository
 * @property BaseService $service
 * @property Cacheable $cache
 */
trait BusinessGenerator
{
    private ?string $businessModel = null;

    public function __get(string $name)
    {
        return match ($name) {
            Sign::Repository => $this->makeBusinessModel(Sign::Repository),
            Sign::Service    => $this->makeBusinessModel(Sign::Service),
            Sign::Cache      => $this->makeBusinessModel(Sign::Cache, ['repositories' => $this->repository]),
        };
    }

    private function makeBusinessModel(string $business, array $params = []): BaseService|Repositories|Cacheable
    {
        $class = $this->checkBusiness($business);

        ['class' => $parentClass, 'type' => $type] = match ($business) {
            Sign::Repository => ['class' => Repositories::class, 'type' => '仓库'],
            Sign::Service    => ['class' => BaseService::class, 'type' => '服务'],
            Sign::Cache      => ['class' => Cacheable::class, 'type' => '缓存'],
        };

        if (!is_subclass_of($class, $parentClass)) {
            throw new \RuntimeException('获取' . $type . '模型[ ' . $class . ' ]错误,必须继承: [' . $parentClass . ' ]');
        }

        try {
            return !empty($params) ? app($class, $params) : app($class);
        } catch (\Exception $e) {
            throw new \RuntimeException('实例化' . $type . '模型出错:[ ' . $e->getMessage() . ' ]');
        }
    }

    private function checkBusiness(?string $business): string
    {
        if (!is_null($this->businessModel)) {

            if (str_ends_with($this->businessModel, ucwords($business))) {
                return $this->businessModel;
            }

            $this->refreshBusinessModel();
        }

        $class = $this->getBusinessModel($business);

        if (!class_exists($class)) {

            $msg = match (true) {
                str_ends_with($class, 'Request')    => '验证',
                str_ends_with($class, 'Service')    => '服务',
                str_ends_with($class, 'Repository') => '仓库',
                str_ends_with($class, 'Cache')      => '缓存'
            };

            throw new \RuntimeException($msg . '模型[ ' . $class . '] 不存在');
        }

        return $this->businessModel = $class;
    }

    private function getBusinessModel(string $business = Sign::Request): string
    {
        $class = str_replace('\\Http\\Controllers\\', '\\', get_called_class());

        $parts = explode('\\', $class);

        $ns = match ($business) {
            Sign::Service    => array_shift($parts) . '\\Services\\',
            Sign::Repository => array_shift($parts) . '\\Repositories\\',
            Sign::Request    => array_shift($parts) . '\\Http\\Requests\\',
            Sign::Cache      => array_shift($parts) . '\\Caches\\',
        };

        $class = array_pop($parts);
        $class = strEndWith($class, 'Controller');

        return $ns . $class . ucwords($business);
    }

    final protected function validatedForm(array $extras = []): array
    {
        $this->checkBusiness(Sign::Request);

        if (!is_subclass_of($this->businessModel, FormRequest::class)) {
            throw new \RuntimeException('无法验证表单', 500);
        }

        if (!empty($extras)) {
            $this->request->merge($extras);
            return app($this->businessModel)->all();
        }

        return app($this->businessModel)->validated();
    }

    final protected function refreshBusinessModel(string $class = null): void
    {
        $this->businessModel = $class;
    }
}
