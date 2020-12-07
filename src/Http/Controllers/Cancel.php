<?php

namespace LaravelEnso\DataExport\Http\Controllers;

use Illuminate\Routing\Controller;
use LaravelEnso\DataExport\Models\DataExport;

class Cancel extends Controller
{
    public function __invoke(DataExport $export)
    {
        $export->cancel();

        return ['message' => __('The export was cancelled successfully')];
    }
}
