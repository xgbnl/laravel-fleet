<?php

namespace Xgbnl\Fleet\Providers;

use Illuminate\Support\ServiceProvider;
use Xgbnl\Fleet\Commands\InstallCommand;
use Xgbnl\Fleet\Commands\MakeCacheCommand;
use Xgbnl\Fleet\Commands\MakeObserverCommand;
use Xgbnl\Fleet\Commands\MakeRepositoryCommand;
use Xgbnl\Fleet\Commands\TransformCommand;

 class FleetServiceProvider extends ServiceProvider
{
    protected array $commands = [
        InstallCommand::class,
        MakeCacheCommand::class,
        MakeRepositoryCommand::class,
        TransformCommand::class,
        MakeObserverCommand::class,
    ];

    public function boot(): void
    {
        $this->installCommand($this->commands);
    }

    // Install BaseController
    protected function installCommand(array $commands): void
    {
        $this->publishes([dirname(__DIR__) . '/Commands/Stubs/BaseController.stub' => app_path('Http/Controllers/BaseController.php')]);
        $this->commands($commands);
    }

    public function provides(): array
    {
        return ['fleet']; // TODO: Change the autogenerated stub
    }
}
