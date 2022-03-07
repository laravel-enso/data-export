<?php

namespace LaravelEnso\DataExport\Services;

use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\XLSX\Writer;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaravelEnso\DataExport\Contracts\AfterHook;
use LaravelEnso\DataExport\Contracts\BeforeHook;
use LaravelEnso\DataExport\Contracts\CustomRowAction;
use LaravelEnso\DataExport\Contracts\ExportsExcel;
use LaravelEnso\DataExport\Contracts\Notifies;
use LaravelEnso\DataExport\Enums\Statuses;
use LaravelEnso\DataExport\Models\Export;
use LaravelEnso\DataExport\Notifications\ExportDone;
use LaravelEnso\DataExport\Notifications\ExportError;
use LaravelEnso\Files\Models\File;
use LaravelEnso\Files\Models\Type;
use LaravelEnso\Helpers\Services\OptimalChunk;
use Throwable;

class ExcelExport
{
    private const Extension = 'xlsx';

    private string $savedName;
    private int $rowLimit;
    private Writer $writer;
    private int $currentChunk;
    private int $currentSheet;

    public function __construct(
        private Export $export,
        private ExportsExcel $exporter
    ) {
        $this->savedName = $this->savedName();
        $this->rowLimit = Config::get('enso.exports.rowLimit');
    }

    public function handle()
    {
        try {
            $this->export();
        } catch (Throwable $throwable) {
            $this->failed();
            throw $throwable;
        }

        return $this;
    }

    private function export()
    {
        $this->before()
            ->initWriter()
            ->start()
            ->addHeading()
            ->addRows();

        if ($this->export->fresh()->cancelled()) {
            $this->closeWriter();
        } else {
            $this->finalize()
                ->after()
                ->notify();
        }
    }

    private function before(): self
    {
        if ($this->exporter instanceof BeforeHook) {
            $this->exporter->before($this->export);
        }

        return $this;
    }

    private function initWriter(): self
    {
        $this->writer = WriterEntityFactory::createXLSXWriter();

        $defaultStyle = (new StyleBuilder())
            ->setShouldWrapText(false)
            ->build();

        $this->writer->setDefaultRowStyle($defaultStyle)
            ->openToFile(Storage::path($this->path()));

        return $this;
    }

    private function start(): self
    {
        $this->export->update(['status' => Statuses::Processing]);

        return $this;
    }

    private function addHeading(): self
    {
        $this->writer->addRow(
            $this->row($this->exporter->heading())
        );

        $this->currentSheet = 0;

        return $this;
    }

    private function addRows(): self
    {
        $chunk = OptimalChunk::get($this->export->total, $this->rowLimit);

        $this->currentChunk = 0;

        $this->exporter->query()
            ->select($this->exporter->attributes())
            ->chunkById($chunk, fn ($rows) => $this->addChunk($rows));

        return $this;
    }

    private function addChunk(Collection $rows)
    {
        if ($this->export->fresh()->cancelled()) {
            return false;
        } else {
            $rows->each(fn ($row) => $this->addRow($row));

            $this->updateProgress();
        }
    }

    private function addRow($row)
    {
        if ($this->needsNewSheet()) {
            $this->addSheet();
        }

        if ($this->exporter instanceof CustomRowAction) {
            $this->exporter->customRowAction($this->writer, $row);
        } else {
            $this->writer->addRow($this->row($this->exporter->mapping($row)));
        }

        $this->currentChunk++;
        $this->currentSheet++;
    }

    private function row($row): Row
    {
        return WriterEntityFactory::createRowFromArray($row);
    }

    private function addSheet(): void
    {
        $this->writer->addNewSheetAndMakeItCurrent();
        $this->addHeading();
    }

    private function updateProgress(): void
    {
        $this->export->updateProgress($this->currentChunk);
        $this->currentChunk = 0;
    }

    private function finalize(): self
    {
        $this->closeWriter();

        $args = [
            $this->export, $this->savedName,
            $this->exporter->filename(), $this->export->created_by,
        ];

        $file = File::attach(...$args);

        $this->export->fill(['status' => Statuses::Finalized])
            ->file()->associate($file)
            ->save();

        return $this;
    }

    private function after(): self
    {
        if ($this->exporter instanceof AfterHook) {
            $this->exporter->after($this->export);
        }

        return $this;
    }

    private function notify(): void
    {
        if ($this->exporter instanceof Notifies) {
            $this->exporter->notify($this->export);
        } else {
            $this->notifiables()
                ->each->notify((new ExportDone($this->export, $this->emailSubject()))
                    ->onQueue('notifications'));
        }
    }

    protected function notifyError(): void
    {
        $this->notifiables()
            ->each->notify((new ExportError($this->export, $this->emailSubject()))
                ->onQueue('notifications'));
    }

    private function needsNewSheet(): bool
    {
        return $this->currentSheet === $this->rowLimit;
    }

    private function savedName(): string
    {
        $hash = Str::random(40);
        $extension = self::Extension;

        return "{$hash}.{$extension}";
    }

    private function path(): string
    {
        return Type::for($this->export::class)->path($this->savedName);
    }

    private function notifiables(): Collection
    {
        return method_exists($this->exporter, 'notifiables')
            ? $this->exporter->notifiables($this->export)
            : Collection::wrap($this->export->createdBy);
    }

    private function emailSubject(): ?string
    {
        return method_exists($this->exporter, 'emailSubject')
            ? $this->exporter->emailSubject($this->export)
            : null;
    }

    private function failed(): void
    {
        $this->export->update(['status' => Statuses::Failed]);
        Storage::delete($this->path());
        $this->notifyError();
        $this->closeWriter();
    }

    private function closeWriter(): void
    {
        if (isset($this->writer)) {
            $this->writer->close();
        }
    }
}
