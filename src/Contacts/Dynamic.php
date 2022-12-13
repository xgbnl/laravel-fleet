<?php

namespace Xgbnl\Fleet\Contacts;

interface Dynamic
{
    public function get(string $name): mixed;
}
