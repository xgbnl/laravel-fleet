<?php

namespace Xgbnl\Fleet\Decorates\Contacts;

interface ImageObjectDecorate extends Decorate
{
    /**
     * 组合域名
     * @param mixed $files
     * @param string $domain 域名
     * @return string|array
     */
    public function endpoint(mixed $files, string $domain): string|array;

    /**
     * 移除域名
     * @param mixed $files 文件/图像路径
     * @param string $domain 域名
     * @return string|array
     */
    public function removeEndpoint(mixed $files, string $domain): string|array;
}