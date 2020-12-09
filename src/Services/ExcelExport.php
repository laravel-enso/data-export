<?php

namespace LaravelEnso\DataExport\Services;

use Box\Spout\Common\Entity\Row;
use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\XLSX\Writer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaravelEnso\DataExport\Contracts\AfterExportHook;
use LaravelEnso\DataExport\Contracts\BeforeExportHook;
use LaravelEnso\DataExport\Contracts\ExportsExcel;
use LaravelEnso\DataExport\Contracts\Notifies;
use LaravelEnso\DataExport\Contracts\NotifiesEntities;
use LaravelEnso\DataExport\Enums\Statuses;
use LaravelEnso\DataExport\Models\DataExport;
use LaravelEnso\DataExport\Notifications\ExportDone;

class ExcelExport
{
    private const Extension = 'xlsx';

    private DataExport $export;
    private ExportsExcel $exporter;
    private string $path;
    private Writer $writer;
    private int $currentChunk;

    public function __construct(DataExport $export, ExportsExcel $exporter)
    {
        $this->export = $export;
        $this->exporter = $exporter;
        $this->path = $this->path();
    }

    public function handle()
    {
        $this->before()
            ->initWriter()
            ->start()
            ->addHeading()
            ->addRows()
            ->finalize()
            ->after()
            ->notify();

        return $this;
    }

    private function before(): self
    {
        if ($this->exporter instanceof BeforeExportHook) {
            $this->exporter->before();
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
            ->openToFile(Storage::path($this->path));

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

        $this->currentChunk = 0;

        return $this;
    }

    private function addRows(): self
    {
        $chunk = Config::get('enso.exports.chunk');

        $this->exporter->query()
            ->select($this->exporter->attributes())
            ->chunkById($chunk, fn ($rows) => $this->addChunk($rows)
                ->updateProgress());

        return $this;
    }

    private function addChunk(Collection $rows): self
    {
        $rows->each(fn ($row) => $this->addRow($row));

        return $this;
    }

    private function addRow($row)
    {
        if ($this->needsNewSheet()) {
            $this->addSheet();
        }

        $this->writer->addRow($this->row($this->exporter->mapping($row)));
        $this->currentChunk++;
    }

    private function addSheet(): void
    {
        $this->writer->addNewSheetAndMakeItCurrent();
        $this->addHeading();
    }

    private function updateProgress(): void
    {
        $this->export->updateProgress($this->currentChunk);
    }

    private function finalize(): self
    {
        $this->writer->close();

        $filename = $this->exporter->filename();
        $this->export->file->created_by = $this->export->created_by;
        $this->export->file->attach($this->path, $filename);

        $this->export->update(['status' => Statuses::Finalized]);

        return $this;
    }

    private function after(): self
    {
        if ($this->exporter instanceof AfterExportHook) {
            $this->exporter->after();
        }

        return $this;
    }

    private function notify()
    {
        if (! $this->exporter instanceof Notifies) {
            return;
        }

        $entities = $this->exporter instanceof NotifiesEntities
            ? $this->exporter->entities()
            : $this->export->createdBy;

        Collection::wrap($entities)->each(fn ($entity) => $entity
            ->notify(
                (new ExportDone($this->export))->onQueue('notifications')
            ));
    }

    private function needsNewSheet(): bool
    {
        return $this->currentChunk === (int) Config::get('enso.exports.rowLimit');
    }

    private function path(): string
    {
        $hash = Str::random(40);
        $extension = self::Extension;

        return "{$this->export->folder()}/{$hash}.{$extension}";
    }

    private function row($row): Row
    {
        return WriterEntityFactory::createRowFromArray($row);
    }
}
