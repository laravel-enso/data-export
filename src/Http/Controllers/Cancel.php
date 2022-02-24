<?php

namespace LaravelEnso\DataExport\Http\Controllers;

use Illuminate\Routing\Controller;
use LaravelEnso\DataExport\Models\Export;

class Cancel extends Controller
{
    public function __invoke(Export $export)
    {
        $export->cancel();

        return ['message' => __('The export was cancelled successfully')];
    }
}
