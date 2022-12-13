<?php

namespace Xgbnl\Fleet\Enum;

enum Trigger: string
{
    final public const Created = 'created';

    final public const Updated = 'updated';

    final public const Deleted = 'deleted';
}
