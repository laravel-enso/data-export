<?php

namespace LaravelEnso\DataExport\app\Enums;

use LaravelEnso\Helpers\app\Classes\Enum;

class Statuses extends Enum
{
    const Waiting = 10;
    const Processing = 20;
    const Finalized = 30;
}
