<?php

namespace LaravelEnso\DataExport\Commands;

use Illuminate\Console\Command;
use LaravelEnso\DataExport\Models\DataExport;

class Purge extends Command
{
    protected $signature = 'enso:data-export:purge';

    protected $description = 'Removes exports old exports';

    public function handle()
    {
        DataExport::expired()->get()->each->delete();
    }
}
