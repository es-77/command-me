<?php

namespace EmmanuelSaleem\CommandMe;

use Illuminate\Support\ServiceProvider;

class CommandMeServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->commands([
            \EmmanuelSaleem\CommandMe\Console\Commands\GenerateClasses::class,
        ]);
    }

    
    public function boot()
    {
        // Bootstrapping logic if needed.
    }
}
