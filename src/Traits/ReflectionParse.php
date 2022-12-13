<?php

namespace Xgbnl\Fleet\Traits;

use ReflectionClass;
use ReflectionException;

trait ReflectionParse
{
    /**
     * @throws ReflectionException
     */
    public function __call($method, $parameters)
    {
        $model = array_filter($this->parseAttributes(), fn($object) => method_exists($object, $method));

        if (!method_exists($this, $method) && empty($model)) {
            throw new \RuntimeException('控制器[ ' . get_class($this) . '] 不存在方法 [ ' . $method . ' ]', 500);
        }

        $reflection = new ReflectionClass(array_pop($model));

        return !$reflection->hasMethod($method)
            ? parent::__call($method, $parameters)
            : $this->prepareMethod($reflection, $method, $parameters);
    }

    /**
     * @throws ReflectionException
     */
    private function prepareMethod(ReflectionClass $reflectionClass, string $method, array $parameters)
    {
        $magicMethod = $reflectionClass->getMethod($method);

        $params = [];

        foreach ($magicMethod->getParameters() as $key => $parameter) {
            $params[] = match (true) {
                isset($parameters[$parameter->getName()])                            => $parameters[$parameter->getName()],
                ($parameter->isDefaultValueAvailable() && !isset($parameters[$key])) => $parameter->getDefaultValue(),
                isset($parameters[$key])                                             => $parameters[$key],
            };
        }

        if ($magicMethod->isPrivate()) {
            throw new \RuntimeException('调用的方法:[ ' . $magicMethod . ' ]是类私的，调用失败', 500);
        }

        if ($magicMethod->isProtected()) {
            throw new \RuntimeException('调用的方法:[ ' . $magicMethod . ' ]受保护，调用失败', 500);
        }

        if ($magicMethod->isAbstract()) {
            throw new \RuntimeException('调用的方法:[ ' . $magicMethod . ' ]是抽象方法，调用失败', 500);
        }

        if (!$reflectionClass->isInstantiable()) {
            throw new \RuntimeException('目录类[ ' . $reflectionClass->getName() . ' ]无法被实例化', 500);
        }

        return $magicMethod->isStatic()
            ? $magicMethod->invoke(null, ...$params)
            : $magicMethod->invoke($reflectionClass->newInstance(), ...$params);
    }

    protected function parseAttributes(): array
    {
        $class = new ReflectionClass(self::class);

        $traits = $class->getTraits();
        $trait = $traits[CallMethodCollection::class];

        $refAttributes = $trait->getAttributes();

        if (empty($refAttributes)) {
            return [];
        }

        $arguments = array_map(fn($attr) => $attr->getArguments(), $refAttributes);

        return $this->flatten($arguments);
    }

    protected function flatten(array $array, array $result = []): array
    {
        foreach ($array as $item) {
            is_array($item)
                ? ($result = $this->flatten($item, $result))
                : (!empty($item) ? $result[] = $item : $result = $item);
        }

        return $result;
    }
}