<?php

namespace EmmanuelSaleem\CommandMe\Console\Commands;

use Illuminate\Console\Command;

class InspireCommand extends Command
{
    protected $signature = 'package:inspire';

    protected $description = 'Inspire command from the custom package';

    public function handle()
    {
        $this->info("Stay inspired!");
    }
}
