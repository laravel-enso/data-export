<?php

namespace LaravelEnso\DataExport\Enums;

use LaravelEnso\Enums\Services\Enum;

class Statuses extends Enum
{
    public const Waiting = 10;
    public const Processing = 20;
    public const Finalized = 30;
    public const Cancelled = 40;
    public const Failed = 50;

    protected static array $data = [
        self::Waiting => 'waiting',
        self::Processing => 'processing',
        self::Finalized => 'finalized',
        self::Cancelled => 'cancelled',
        self::Failed => 'failed',
    ];

    public static function running(): array
    {
        return [self::Waiting, self::Processing];
    }

    public static function deletable(): array
    {
        return [self::Finalized, self::Cancelled, self::Failed];
    }

    public static function isDeletable(int $status): bool
    {
        return in_array($status, self::deletable());
    }
}
