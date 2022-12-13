<?php

namespace Xgbnl\Fleet\Attributes;

use Attribute;

#[Attribute]
readonly class BusinessTag
{
    public string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }
}