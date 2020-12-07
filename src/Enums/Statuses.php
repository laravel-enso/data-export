<?php

namespace LaravelEnso\DataExport\Enums;

use LaravelEnso\Enums\Services\Enum;

class Statuses extends Enum
{
    public const Waiting = 10;
    public const Processing = 20;
    public const Finalized = 30;
    public const Cancelled = 40;

    protected static array $data = [
        self::Waiting => 'waiting',
        self::Processing => 'processing',
        self::Finalized => 'finalized',
        self::Cancelled => 'cancelled',
    ];

    public static function isCancellable(int $status): bool
    {
        return in_array($status, self::cancellable());
    }

    public static function cancellable(): array
    {
        return [static::Waiting, static::Processing];
    }
}
