<?php

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LaravelEnso\DataExport\Contracts\CustomChunkSize;
use LaravelEnso\DataExport\Contracts\ExportsExcel;
use LaravelEnso\DataExport\Models\Export;
use LaravelEnso\DataExport\Services\Iterator;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IteratorTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_subdivides_dense_ranges_without_skipping_records(): void
    {
        $exports = Export::factory()->count(3)->create()->sortBy('id');
        config(['enso.exports.optimalChunk' => 1]);
        $iterator = new Iterator(new class implements CustomChunkSize, ExportsExcel {
            public function chunkSize(): int
            {
                return 1;
            }

            public function filename(): string
            {
                return 'iterator';
            }

            public function heading(): array
            {
                return [];
            }

            public function query(): Builder
            {
                return Export::query();
            }

            public function attributes(): array
            {
                return ['id'];
            }

            public function mapping($row): array
            {
                return [];
            }
        });
        $ids = collect();
        $chunkSizes = collect();

        while ($iterator->valid()) {
            $chunk = $iterator->current();
            $ids = $ids->merge($chunk->pluck('id'));
            $chunkSizes->push($chunk->count());
            $iterator->next();
        }

        $this->assertSame($exports->pluck('id')->values()->all(), $ids->all());
        $this->assertSame([1, 1, 1], $chunkSizes->all());
    }
}
