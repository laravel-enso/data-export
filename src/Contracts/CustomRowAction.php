<?php

namespace LaravelEnso\DataExport\Contracts;

use OpenSpout\Writer\XLSX\Writer;

interface CustomRowAction
{
    public function customRowAction(Writer $writer, $row): void;
}
