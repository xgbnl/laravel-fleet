<?php

declare(strict_types=1);

namespace Xgbnl\Fleet\Decorates;

use Xgbnl\Fleet\Attributes\BusinessTag;
use Xgbnl\Fleet\Decorates\Contacts\Decorate;
use Xgbnl\Fleet\Decorates\Contacts\ImageObjectDecorate;

#[BusinessTag('字符串包装器')]
readonly class StringDecorate extends ArrayDecorate implements Decorate, ImageObjectDecorate
{
    public function filter(array $origin, mixed $fields): array
    {
        if (isset($origin[$fields])) {
            unset($origin[$fields]);
        }

        return $origin;
    }

    public function arrayFields(array $origin, mixed $fields): array
    {
        return (!isset($origin[$fields])) ? [] : [$fields => $origin[$fields]];
    }

    public function endpoint(mixed $files, string $domain): string|array
    {
        return $this->appendSymbol($domain, $files);
    }

    public function removeEndpoint(mixed $files, string $domain): string
    {
        return $this->replaceEndpoint($files, $domain);
    }
}