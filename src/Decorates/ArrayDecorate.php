<?php

declare(strict_types=1);

namespace Xgbnl\Fleet\Decorates;

use Xgbnl\Fleet\Attributes\BusinessTag;
use Xgbnl\Fleet\Decorates\Contacts\Decorate;
use Xgbnl\Fleet\Decorates\Contacts\ImageObjectDecorate;

#[BusinessTag('数组包装器')]
readonly class ArrayDecorate extends AbstractDecorate implements Decorate, ImageObjectDecorate
{
    public function filter(array $origin, mixed $fields): array
    {
        foreach ($fields as $field) {
            if (isset($origin[$field])) {
                unset($origin[$field]);
            }
        }
        unset($field);

        return $origin;
    }

    public function arrayFields(array $origin, mixed $fields): array
    {
        $items = [];

        array_map(function ($field) use ($origin, &$items) {
            if (isset($origin[$field])) {
                $items[$field] = $origin[$field];
            }
        }, $fields);

        return $items;
    }

    public function endpoint(mixed $files, string $domain): string|array
    {
        return array_map(fn($file) => $this->appendSymbol($domain, $file), $files);
    }

    public function removeEndpoint(mixed $files, string $domain): string|array
    {
        return array_map(fn($path) => $this->replaceEndpoint($path, $domain), $files);
    }
}