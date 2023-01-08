<?php

namespace LaravelEnso\DataExport\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use LaravelEnso\DataExport\Contracts\CustomMax;
use LaravelEnso\DataExport\Contracts\CustomMin;
use LaravelEnso\DataExport\Contracts\ExportsExcel;

class Iterator
{
    private Builder $query;
    private string $primaryKey;
    private int $min;
    private int $max;

    public function __construct(
        private ExportsExcel $exporter,
        private int $chunkSize
    ) {
        $this->query = $this->exporter->query()
            ->select($this->exporter->attributes());

        $this->primaryKey = $this->query->getModel()->getKeyName();
    }

    public function valid(): bool
    {
        $this->min ??= $this->min();

        $this->max ??= $this->max();

        return $this->min <= $this->max;
    }

    public function current(): Collection
    {
        return $this->query->clone()
            ->where($this->primaryKey, '>=', $this->min)
            ->where($this->primaryKey, '<', $this->min + $this->chunkSize)
            ->get();
    }

    public function next(): void
    {
        $this->min += $this->chunkSize;
    }

    private function min(): int
    {
        $min = $this->exporter instanceof CustomMin
            ? $this->exporter->min()
            : $this->query->min($this->primaryKey);

        return $min ?? 1;
    }

    private function max(): int
    {
        $max = $this->exporter instanceof CustomMax
            ? $this->exporter->max()
            : $this->query->max($this->primaryKey);

        return $max ?? 0;
    }
}
