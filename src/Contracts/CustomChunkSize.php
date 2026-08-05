<?php

namespace LaravelEnso\DataExport\Contracts;

interface CustomChunkSize
{
    public function chunkSize(): int;
}
