<?php

namespace EmmanuelSaleem\CommandMe;

use Illuminate\Support\ServiceProvider;

class CommandMeServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // Register the command.
        $this->commands([
            \EmmanuelSaleem\CommandMe\Console\Commands\InspireCommand::class,
            \EmmanuelSaleem\CommandMe\Console\Commands\GenerateClasses::class,
        ]);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        // Bootstrapping logic if needed.
    }
}
