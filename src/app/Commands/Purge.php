<?php

namespace LaravelEnso\DataExport\App\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use LaravelEnso\DataExport\app\Models\DataExport;

class Purge extends Command
{
    protected $signature = 'enso:data-export:purge';

    protected $description = 'Removes exports older than 2 months';

    public function handle()
    {
        $retainFor = Config::get('enso.exports.retainFor');

        DataExport::where('created_at', '<', Carbon::today()->subDays($retainFor))
            ->get()->each->delete();
    }
}
