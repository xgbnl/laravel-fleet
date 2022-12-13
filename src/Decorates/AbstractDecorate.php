<?php

namespace Xgbnl\Fleet\Decorates;

abstract readonly class AbstractDecorate
{
    // 替换掉域名
    final protected function replaceEndpoint(string $haystack, string $domain, string $needle = '/'): string
    {
        $haystack = str_contains($haystack, $domain) ? str_replace($domain, '', $haystack) : $haystack;

        if (str_starts_with($haystack, $needle)) {
            $haystack = substr($haystack, 1, strlen($haystack) - 1);
        }
        return $haystack;
    }

    final protected function appendSymbol(string $domain, string $path, $symbol = '/'): string
    {
        if (str_starts_with($path, $symbol) && str_ends_with($domain, $symbol)) {
            return strstr($domain, 0, strlen($domain) - 1) . $path;
        }

        if (!str_starts_with($path,$symbol) && !str_ends_with($domain,$symbol)) {
            return $domain.'/'.$path;
        }

        return $domain.$path;
    }
}