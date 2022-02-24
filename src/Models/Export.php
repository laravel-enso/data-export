<?php

namespace LaravelEnso\DataExport\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use LaravelEnso\DataExport\Contracts\ExportsExcel as AsyncExcel;
use LaravelEnso\DataExport\Enums\Statuses;
use LaravelEnso\DataExport\Exceptions\Exception;
use LaravelEnso\DataExport\Notifications\ExportDone;
use LaravelEnso\DataExport\Services\ExcelExport as AsyncExporter;
use LaravelEnso\Excel\Contracts\ExportsExcel as SyncExcel;
use LaravelEnso\Excel\Services\ExcelExport as SyncExporter;
use LaravelEnso\Files\Contracts\Attachable;
use LaravelEnso\Files\Models\File;
use LaravelEnso\Helpers\Services\Decimals;
use LaravelEnso\IO\Contracts\IOOperation;
use LaravelEnso\IO\Enums\IOTypes;
use LaravelEnso\Tables\Notifications\ExportStarted;
use LaravelEnso\TrackWho\Traits\CreatedBy;
use UnexpectedValueException;

class Export extends Model implements Attachable, IOOperation
{
    use CreatedBy, HasFactory;

    protected $guarded = [];

    protected $table = 'data_exports';

    public function file(): Relation
    {
        return $this->belongsTo(File::class);
    }

    public function cancel(): void
    {
        if (! $this->running()) {
            throw Exception::cannotBeCancelled();
        }

        $this->update(['status' => Statuses::Cancelled]);
    }

    public function cancelled(): bool
    {
        return $this->status === Statuses::Cancelled;
    }

    public function failed(): bool
    {
        return $this->status === Statuses::Failed;
    }

    public function running(): bool
    {
        return in_array($this->status, Statuses::running());
    }

    public function finalized(): bool
    {
        return $this->status === Statuses::Finalized;
    }

    public function operationType(): int
    {
        return IOTypes::Export;
    }

    public function status(): int
    {
        return $this->running()
            ? $this->status
            : Statuses::Finalized;
    }

    public function progress(): ?int
    {
        if (! $this->total) {
            return null;
        }

        $div = Decimals::div($this->entries, $this->total);

        return (int) Decimals::mul($div, 100);
    }

    public function broadcastWith(): array
    {
        return [
            'name' => $this->name,
            'entries' => $this->entries,
            'total' => $this->total,
        ];
    }

    public function createdAt(): Carbon
    {
        return $this->created_at;
    }

    public static function excel($exporter): self
    {
        if ($exporter instanceof AsyncExcel) {
            return self::asyncExcel($exporter);
        }
        if ($exporter instanceof SyncExcel) {
            return self::syncExcel($exporter);
        }

        throw new UnexpectedValueException(
            __('The exporter class must be in instance of ExportsExcel interface')
        );
    }

    private static function asyncExcel(AsyncExcel $exporter): self
    {
        $export = self::factory()->create([
            'name' => $exporter->filename(),
            'total' => $exporter->query()->count(),
        ]);

        (new AsyncExporter($export, $exporter))->handle();

        return $export;
    }

    private static function syncExcel(SyncExcel $exporter): self
    {
        $export = self::factory()->create([
            'name' => $exporter->filename(),
            'status' => Statuses::Processing,
            'total' => 0,
        ]);

        $export->createdBy->notify((new ExportStarted($export->name))
            ->onQueue('notifications'));

        $count = Collection::wrap($exporter->sheets())
            ->reduce(fn ($total, $sheet) => $total += count($exporter->rows($sheet)), 0);

        $export->updateProgress($count);

        $path = Str::afterLast((new SyncExporter($exporter))->save(), 'app/');
        $args = [$export, $path, $exporter->filename(), $export->created_by];
        $file = File::attach(...$args);

        $export->fill(['status' => Statuses::Finalized])
            ->file()->associate($file)
            ->save();

        $export->createdBy->notify((new ExportDone($export))
            ->onQueue('notifications'));

        return $export;
    }

    public function updateProgress(int $entries)
    {
        $this->entries += $entries;
        $this->total = max($this->total, $this->entries);
        $this->save();
    }

    public function scopeExpired(Builder $query): Builder
    {
        $retainFor = Config::get('enso.exports.retainFor');
        $expired = Carbon::today()->subDays($retainFor);

        return $query->where('created_at', '<', $expired);
    }
}
