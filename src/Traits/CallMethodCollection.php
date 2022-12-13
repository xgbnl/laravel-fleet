<?php

namespace Xgbnl\Fleet\Traits;

use Xgbnl\Fleet\Attributes\Business;
use Xgbnl\Fleet\Utils\CustomMethods;

/**
 * @method array filter(array $arrays) 过滤数组中值为空的项
 * @method array filterFields(array $haystack, string|array $fields, bool $returnOrigin = true) 过滤移除指定字段，returnOrigin为true时返回移除指定字段后的原数组，给定false时返回参数fields的值
 * @method void  trigger(int $code, string $message) 触发一个自定义的异常
 * @method mixed endpoint(mixed $needle, string $domain, bool $replace = false) 为图像添加或移除域名
 * @method array customMerge(array $haystack, array $needle) 自定义合并数组
 * @method string customSubStr(string $haystack, string $symbol, bool $tail = false) 截取字符串开头或结尾的串
 * @method array generateTree(array $list, string $id = 'id', string $pid = 'pid', string $son = 'children') 为列表生成树结构
 */
#[Business(CustomMethods::class)]
trait CallMethodCollection
{
    use ReflectionParse;
}