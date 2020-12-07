<?php

namespace LaravelEnso\DataExport\Services;

use Box\Spout\Writer\Common\Creator\Style\StyleBuilder;
use Box\Spout\Writer\Common\Creator\WriterEntityFactory;
use Box\Spout\Writer\XLSX\Writer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use LaravelEnso\DataExport\Contracts\AfterExportHook;
use LaravelEnso\DataExport\Contracts\BeforeExportHook;
use LaravelEnso\DataExport\Contracts\ExportsExcel;
use LaravelEnso\DataExport\Enums\Statuses;
use LaravelEnso\DataExport\Models\DataExport;

class ExcelExport
{
    private const Extension = 'xlsx';

    private DataExport $export;
    private ExportsExcel $exporter;
    private string $path;
    private int $rowLimit;
    private int $chunk;
    private Writer $writer;
    private int $count;
    private int $sheetCount;

    public function __construct(DataExport $export, ExportsExcel $exporter)
    {
        $this->export = $export;
        $this->exporter = $exporter;
        $this->count = 0;
        $this->sheetCount = 1;
        $this->rowLimit = config('enso.exports.rowLimit');
        $this->chunk = config('enso.exports.chunk');
    }

    public function count()
    {
        return $this->count;
    }

    public function rowLimit(int $limit)
    {
        $this->rowLimit = $limit;

        return $this;
    }

    public function chunk(int $chunk)
    {
        $this->chunk = $chunk;

        return $this;
    }

    public function handle()
    {
        $this->before()
            ->initWriter()
            ->start()
            ->addHeading()
            ->addRows()
            ->finalize()
            ->after();

        return $this;
    }

    private function before()
    {
        if ($this->exporter instanceof BeforeExportHook) {
            $this->exporter->before();
        }

        return $this;
    }

    private function initWriter()
    {
        $this->writer = WriterEntityFactory::createXLSXWriter();

        $defaultStyle = (new StyleBuilder())
            ->setShouldWrapText(false)
            ->build();

        $this->writer->setDefaultRowStyle($defaultStyle)
            ->openToFile($this->path());

        return $this;
    }

    private function start()
    {
        $this->export->update(['status' => Statuses::Processing]);

        return $this;
    }

    private function addHeading()
    {
        $this->writer->addRow(
            $this->row($this->exporter->heading())
        );

        return $this;
    }

    private function addRows()
    {
        $this->exporter->query()
            ->select($this->exporter->attributes())
            ->chunkById($this->chunk, fn ($rows) => $this->addChunk($rows)
                ->updateProgress());

        return $this;
    }

    private function addChunk(Collection $rows)
    {
        $this->count += $rows->count();

        if ($this->needsNewSheet()) {
            $this->addSheet();
        }

        $this->writer->addRows($this->exportRows($rows));

        return $this;
    }

    private function addSheet()
    {
        $this->writer->addNewSheetAndMakeItCurrent();
        $this->addHeading();
        $this->sheetCount++;
    }

    private function exportRows(Collection $rows)
    {
        return $rows->map(fn ($row) => $this->row($this->exporter->mapping($row)))
            ->toArray();
    }

    private function updateProgress()
    {
        $this->export->update(['entries' => $this->count]);
    }

    private function finalize()
    {
        $this->writer->close();

        $filename = $this->exporter->filename();
        $this->export->attach($this->path(), $filename);

        $this->export->file->created_by = $this->export->created_by;
        $this->export->file->save();

        $this->export->update(['status' => Statuses::Finalized]);

        return $this;
    }

    private function after()
    {
        if ($this->exporter instanceof AfterExportHook) {
            $this->exporter->after();
        }

        return $this;
    }

    private function needsNewSheet()
    {
        return $this->count > $this->sheetCount * $this->rowLimit;
    }

    private function path()
    {
        return $this->path ??= Storage::path(
            $this->export->folder()
                .DIRECTORY_SEPARATOR
                .$this->hashName()
        );
    }

    private function hashName()
    {
        return Str::random(40).'.'.self::Extension;
    }

    private function row($row)
    {
        return WriterEntityFactory::createRowFromArray($row);
    }
}
