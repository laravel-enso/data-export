<?php

namespace LaravelEnso\DataExport\Enums;

use LaravelEnso\Enums\Contracts\Frontend;
use LaravelEnso\Enums\Contracts\Mappable;

enum Status: int implements Mappable, Frontend
{
    case Waiting = 10;
    case Processing = 20;
    case Finalized = 30;
    case Cancelled = 40;
    case Failed = 50;

    public function map(): string
    {
        return match ($this) {
            self::Waiting => 'waiting',
            self::Processing => 'processing',
            self::Finalized => 'finalized',
            self::Cancelled => 'cancelled',
            self::Failed => 'failed',
        };
    }

    public function isRunning(): bool
    {
        return match ($this) {
            self::Waiting->value => true,
            self::Processing->value => true,
            self::Finalized->value => false,
            self::Cancelled->value => false,
            self::Failed->value => false,
        };
    }

    public function isDeletable(): bool
    {
        return match ($this) {
            self::Waiting => false,
            self::Processing => false,
            self::Finalized => true,
            self::Cancelled => true,
            self::Failed => true,
        };
    }

    public static function registerBy(): string
    {
        return 'exportStatuses';
    }

    public static function deletable(): array
    {
        return [self::Finalized->value, self::Cancelled->value, self::Failed->value];
    }
}
