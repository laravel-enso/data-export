<?php

namespace LaravelEnso\DataExport\Exceptions;

use LaravelEnso\Helpers\Exceptions\EnsoException;

class Exception extends EnsoException
{
    public static function cannotBeCancelled()
    {
        return new static(__('Only in-progress exports can be cancelled'));
    }
}
