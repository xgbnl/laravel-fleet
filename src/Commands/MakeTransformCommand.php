<?php

namespace Xgbnl\Fleet\Commands;

use Illuminate\Console\GeneratorCommand;

class TransformCommand extends GeneratorCommand
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:transform';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Transform';

    protected $type = 'Transform';

    protected function getStub(): string
    {
        return __DIR__ . '/Stubs/'.$this->type.'.stub';
    }

    protected function getDefaultNameSpace($rootNamespace): string
    {
        return $rootNamespace . '\\' . 'Transforms';
    }
}
