<?php

namespace LaravelEnso\DataExport\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;
use LaravelEnso\DataExport\Contracts\ExportsExcel;
use LaravelEnso\DataExport\Enums\Statuses;
use LaravelEnso\DataExport\Exceptions\Exception;
use LaravelEnso\DataExport\Services\ExcelExport;
use LaravelEnso\Files\Contracts\Attachable;
use LaravelEnso\Files\Contracts\AuthorizesFileAccess;
use LaravelEnso\Files\Traits\FilePolicies;
use LaravelEnso\Files\Traits\HasFile;
use LaravelEnso\Helpers\Services\Decimals;
use LaravelEnso\Helpers\Traits\CascadesMorphMap;
use LaravelEnso\IO\Contracts\IOOperation;
use LaravelEnso\IO\Enums\IOTypes;
use LaravelEnso\TrackWho\Traits\CreatedBy;

class DataExport extends Model implements Attachable, IOOperation, AuthorizesFileAccess
{
    use CascadesMorphMap, CreatedBy, HasFile, HasFactory, FilePolicies;

    protected $guarded = ['id'];

    protected $folder = 'exports';

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
            'filename' => $this->file->original_name,
            'entries' => $this->entries,
            'total' => $this->total,
        ];
    }

    public function createdAt(): Carbon
    {
        return $this->created_at;
    }

    public static function excel(ExportsExcel $exporter): self
    {
        $export = self::factory()->create([
            'name' => $exporter->filename(),
            'total' => $exporter->query()->count(),
        ]);

        (new ExcelExport($export, $exporter))->handle();

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
