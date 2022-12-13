<?php

namespace Xgbnl\Fleet\Cache;

use Redis;
use RedisException;
use Xgbnl\Fleet\Repositories\Repositories;
use Xgbnl\Fleet\Traits\CallMethodCollection;
use Illuminate\Support\Facades\Redis as FacadesRedis;

abstract class Cacheable
{
    use CallMethodCollection;

    protected readonly ?Repositories $repositories;

    protected ?Redis $redis = null;

    final public function __construct(Repositories $repositories = null)
    {
        $this->repositories = $repositories ?? $this->makeRepository();

        $this->connectRedis(env('CACHEABLE', 'default'));
    }

    private function connectRedis(string $connect): void
    {
        try {
            $this->redis = FacadesRedis::connection($connect)->client();
        } catch (RedisException $e) {
            $this->trigger(500, '缓存层redis连接错误:[ ' . $e->getMessage() . ' ],请检查并确认您的配置');
        }
    }

    /**
     * 获取缓存
     * @throws RedisException
     */
    final public function getCache(string $key): mixed
    {
        if (!$this->redis->exists($key)) {
            return [];
        }

        if (!$model = $this->redis->get($key)) {
            return [];
        }

        return json_decode($model, true);
    }

    /**
     * 存储缓存
     * @param string $key
     * @param mixed $caches
     * @return void
     */
    final protected function setCache(string $key, mixed $caches): void
    {
        try {
            $this->redis->set($key, json_encode($caches, JSON_UNESCAPED_UNICODE));
        } catch (RedisException $e) {
            $this->trigger(500, '存储缓存时redis出错: [ ' . $e->getMessage() . ' ]');
        }
    }

    /**
     * 清除缓存
     * @param string $key
     * @return Redis|int
     * @throws RedisException
     */
    final protected function forget(mixed $key): Redis|int
    {
        return $this->redis->exists($key) ? $this->redis->del($key) : 0;
    }

    /**
     * 销毁或所有缓存
     * @throws RedisException
     */
    final static public function destroy(mixed $key): Redis|int
    {
        return (new static())->forget($key);
    }

    private function makeRepository(): Repositories
    {
        $clazz = $this->customSubStr(get_class($this), '\\', true);

        if (str_ends_with($clazz, 'Cache')) {
            $clazz = str_replace('Cache', '', $clazz);
        }

        $class = 'App\\Repositories\\' . $clazz . 'Repository';

        if (!class_exists($class)) {
            $this->trigger(500, '仓库模型[ ' . $clazz . ' ]不存在');
        }

        return app($class);
    }
}
