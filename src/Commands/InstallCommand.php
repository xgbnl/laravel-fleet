<?php

namespace Xgbnl\Fleet\Commands;

use Illuminate\Console\Command;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fleet:publish';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install laravel fleet';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->call('vendor:publish', [
            "--provider" => "Xgbnl\Fleet\FleetServiceProvider"
        ]);
    }
}
