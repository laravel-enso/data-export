<?php

namespace LaravelEnso\DataExport\Enums;

use LaravelEnso\IO\Enums\IOStatus;

enum Status: int
{
    case Waiting = 10;
    case Processing = 20;
    case Finalized = 30;
    case Cancelled = 40;
    case Failed = 50;

    // protected static array $data = [
    //     self::Waiting => 'waiting',
    //     self::Processing => 'processing',
    //     self::Finalized => 'finalized',
    //     self::Cancelled => 'cancelled',
    //     self::Failed => 'failed',
    // ];

    public static function running(): array
    {
        return [self::Waiting, self::Processing];
    }

    public static function deletable(): array
    {
        return [self::Finalized, self::Cancelled, self::Failed];
    }

    public function ioStatus(): IOStatus
    {
        return match ($this) {
            self::Waiting => IOStatus::Started,
            self::Processing => IOStatus::Processing,
            self::Finalized => IOStatus::Finalized,
            self::Cancelled => IOStatus::Finalized, //TODO: discuss
            self::Failed => IOStatus::Finalized, //TODO: discuss
        };
    }

    public function isRunning(): bool
    {
        return in_array($this, self::running());
    }

    public function isDeletable(): bool
    {
        return in_array($this, self::deletable());
    }
}
