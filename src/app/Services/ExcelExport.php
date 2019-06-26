<?php

namespace LaravelEnso\DataExport\app\Services;

use Illuminate\Http\File;
use Box\Spout\Common\Type;
use Illuminate\Support\Str;
use Box\Spout\Writer\WriterFactory;
use Illuminate\Support\Facades\Storage;
use Box\Spout\Writer\Style\StyleBuilder;
use Illuminate\Database\Eloquent\Collection;
use LaravelEnso\DataExport\app\Models\DataExport;
use LaravelEnso\DataExport\app\Contracts\ExportsExcel;
use LaravelEnso\DataExport\app\Contracts\AfterExportHook;
use LaravelEnso\DataExport\app\Contracts\BeforeExportHook;

class ExcelExport
{
    const Extension = 'xlsx';

    private $dataExport;
    private $exporter;
    private $filename;
    private $filePath;
    private $rowLimit;
    private $writer;
    private $count;
    private $sheetCount;

    public function __construct(DataExport $dataExport, ExportsExcel $exporter, string $filename)
    {
        $this->dataExport = $dataExport;
        $this->exporter = $exporter;
        $this->filename = $filename;
        $this->count = 0;
        $this->sheetCount = 1;
        $this->rowLimit = config('enso.exports.rowLimit');
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
        $this->writer = WriterFactory::create(Type::XLSX);

        $defaultStyle = (new StyleBuilder())
            ->setShouldWrapText(false)
            ->build();

        $this->writer->setDefaultRowStyle($defaultStyle)
            ->openToFile($this->filePath());

        return $this;
    }

    private function start()
    {
        $this->dataExport->startProcessing();

        return $this;
    }

    private function addHeading()
    {
        $this->writer->addRow(
            $this->exporter->heading()
        );

        return $this;
    }

    private function addRows()
    {
        $this->exporter->query()
            ->select($this->exporter->attributes())
            ->chunkById(25000, function ($rows) {
                $this->chunk($rows)
                    ->updateProgress();
            });

        return $this;
    }

    private function chunk(Collection $rows)
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
        return $rows->map(function ($row) {
            return $this->exporter->mapping($row);
        })->toArray();
    }

    private function updateProgress()
    {
        $this->dataExport->update([
            'entries' => $this->count,
        ]);
    }

    private function finalize()
    {
        $this->writer->close();

        $this->dataExport->attach(
            new File($this->filePath()), $this->filename
        );

        $this->dataExport->file->created_by = $this->dataExport->created_by;
        $this->dataExport->file->save();
        $this->dataExport->endOperation();

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
        return $this->count % $this->sheetCount > $this->rowLimit;
    }

    private function filePath()
    {
        return $this->filePath
            ?? $this->filePath = Storage::path(
                $this->dataExport->folder()
                    .DIRECTORY_SEPARATOR
                    .$this->hashName()
            );
    }

    private function hashName()
    {
        return Str::random(40).'.'.self::Extension;
    }
}
