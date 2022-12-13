<?php

namespace Xgbnl\Fleet\Attributes;

use Attribute;

#[Attribute]
readonly class Business
{
    public array|string $businessModels;

    public function __construct(array|string $businessModels)
    {
        $this->businessModels = $businessModels;
    }
}