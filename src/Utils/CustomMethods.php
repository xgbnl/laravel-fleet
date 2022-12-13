<?php

declare(strict_types=1);

namespace Xgbnl\Fleet\Utils;

use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use Xgbnl\Fleet\Decorates\Factory\DecorateFactory;
use Xgbnl\Fleet\Paginator\Paginator;

readonly class CustomMethods
{
    /**
     * Custom return json
     * @param mixed|null $data
     * @param int $code
     * @return JsonResponse
     */
    static public function json(mixed $data = null, int $code = 200): JsonResponse
    {
        $r = ['msg' => null, 'code' => $code];

        if (is_string($data)) {
            $r['msg'] = $data;
        } elseif (!is_null($data)) {
            $r['data'] = $data;
        }

        return new JsonResponse($r);
    }

    /**
     * Custom paginate.
     * @param array $list
     * @param bool $isPaginate
     * @return Paginator
     */
    static public function customPaginate(array $list = [], bool $isPaginate = true): Paginator
    {
        $pageNum = (int)request()->get('pageNum', 1);
        $pageSize = (int)request()->get('pageSize', 10);

        $offset = ($pageNum * $pageSize) - $pageSize;

        $items = $isPaginate ? array_slice($list, $offset, $pageSize, true) : $list;

        $total = count($list);

        return new Paginator($items, $total, $pageSize, $pageNum, [
            'path'     => Paginator::resolveCurrentPath(),
            'pageName' => 'pageNum',
        ]);
    }

    /**
     * 过滤为空的项
     * @param array $origin
     * @return array
     */
    static public function filter(array $origin): array
    {
        return array_filter($origin, fn($data) => !empty($data));
    }

    /**
     * 过滤不需要的字段
     * @param array $haystack
     * @param array|string $fields
     * @param bool $returnOrigin
     * @return array
     */
    static public function filterFields(array $haystack, string|array $fields, bool $returnOrigin = true): array
    {
        $decorate = DecorateFactory::builderDecorate($fields);

        return $returnOrigin ? $decorate->filter($haystack, $fields) : $decorate->arrayFields($haystack, $fields);
    }

    /**
     * 触发一个自定义的异常
     * @param int $code
     * @param string $message
     * @return void
     */
    static public function trigger(int $code, string $message): void
    {
        throw new InvalidArgumentException($message, $code);
    }

    /**
     * 触发422表单验证异常
     * @param string $message
     * @return void
     */
    static public function triggerValidate(string $message): void
    {
        self::trigger(422, $message);
    }

    /**
     * 触发401授权异常
     * @param string $message
     * @return void
     */
    static public function triggerAuthorization(string $message): void
    {
        self::trigger(401, $message);
    }

    /**
     * 触发403权限异常
     * @param string $message
     * @return void
     */
    static public function triggerForbidden(string $message): void
    {
        self::trigger(403, $message);
    }

    /**
     * 为图片
     * @param mixed $needle
     * @param string|null $domain
     * @param bool $replace
     * @return array|string
     */
    static public function endpoint(mixed $needle, string $domain = null, bool $replace = false): array|string
    {
        $decorate = DecorateFactory::builderDecorate($needle);

        return $replace ? $decorate->removeEndpoint($needle, $domain) : $decorate->endpoint($needle, $domain);
    }

    /**
     * 合并数组
     * @param array $haystack
     * @param array $needle
     * @return array
     */
    static public function customMerge(array $haystack, array $needle): array
    {
        return array_merge($haystack, $needle);
    }

    /**
     * 截取字符串开头或结尾
     * @param string $haystack 原字符串
     * @param string $symbol 分割部份
     * @param bool $tail 获取尾部字符
     * @return string
     */
    static public function customSubStr(string $haystack, string $symbol, bool $tail = false): string
    {
        return $tail
            ? substr($haystack, strripos($haystack, $symbol) + 1)
            : substr($haystack, 0, strripos($haystack, $symbol));
    }

    /**
     * 生成树结构
     * @param array $list
     * @param string $id
     * @param string $pid
     * @param string $son
     * @return array
     */
    static public function generateTree(array $list, string $id = 'id', string $pid = 'pid', string $son = 'children'): array
    {
        list($tree, $map) = [[], []];
        foreach ($list as $item) {
            $map[$item[$id]] = $item;
        }

        foreach ($list as $item) {
            (isset($item[$pid]) && isset($map[$item[$pid]]))
                ? $map[$item[$pid]][$son][] = &$map[$item[$id]]
                : $tree[] = &$map[$item[$id]];
        }

        unset($map);
        return $tree;
    }
}
