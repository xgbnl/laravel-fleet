<?php

namespace Xgbnl\Fleet\Decorates\Factory;

use Xgbnl\Fleet\Decorates\ArrayDecorate;
use Xgbnl\Fleet\Decorates\Contacts\Decorate;
use Xgbnl\Fleet\Decorates\Contacts\ImageObjectDecorate;
use Xgbnl\Fleet\Decorates\StringDecorate;

readonly class DecorateFactory
{
    static public function builderDecorate(mixed $type): Decorate|ImageObjectDecorate
    {
        return match (true) {
            is_string($type) => new StringDecorate(),
            is_array($type)  => new ArrayDecorate(),
        };
    }
}