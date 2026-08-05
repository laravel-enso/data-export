<?php

namespace LaravelEnso\DataExport\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use LaravelEnso\DataExport\Contracts\CustomMax;
use LaravelEnso\DataExport\Contracts\CustomMin;
use LaravelEnso\DataExport\Contracts\ExportsExcel;
use LaravelEnso\Helpers\Services\OptimalChunk;

class Iterator
{
    private Builder $query;
    private string $primaryKey;
    private int $min;
    private int $max;
    private int $next;
    private int $chunkSize;
    private int $chunkLimit;

    public function __construct(private ExportsExcel $exporter)
    {
        $this->query = $this->exporter->query()
            ->select($this->exporter->attributes());

        $this->primaryKey = $this->query->getModel()->getQualifiedKeyName();
        $this->chunkLimit = Config::get('enso.exports.optimalChunk');

        if ($this->chunkLimit < 1) {
            throw new InvalidArgumentException('The configured export optimal chunk must be positive');
        }
    }

    public function valid(): bool
    {
        $this->min ??= $this->min();
        $this->max ??= $this->max();
        $this->chunkSize ??= OptimalChunk::get($this->max - $this->min + 1);

        return $this->min <= $this->max;
    }

    public function current(): Collection
    {
        $min = $this->min;
        $chunkSize = $this->chunkSize;
        $max = min($min + $chunkSize, $this->max + 1);
        $query = $this->range($min, $max);

        if ($query->count() > $this->chunkLimit) {
            $chunkSize = $this->chunkLimit;
            $max = min($min + $chunkSize, $this->max + 1);
            $query = $this->range($min, $max);
        }

        $this->next = $max;

        return $query->get();
    }

    public function next(): void
    {
        $this->min = $this->next;
    }

    private function range(int $min, int $max): Builder
    {
        return $this->query->clone()
            ->where($this->primaryKey, '>=', $min)
            ->where($this->primaryKey, '<', $max);
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
