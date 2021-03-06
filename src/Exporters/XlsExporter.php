<?php

namespace BadChoice\Reports\Exporters;

use BadChoice\Reports\DataTransformers\Transformers\Currency;
use BadChoice\Reports\DataTransformers\Transformers\Decimal;
use BadChoice\Reports\DataTransformers\Transformers\SheetDecimal;
use Maatwebsite\Excel\Facades\Excel;
use PHPExcel_Style_Color;

class XlsExporter extends BaseExporter
{
    private $file;
    private $excel;

    public function __construct($fields, $collection) {
        parent::__construct($fields, $collection);
        app()->bind(Decimal::class, SheetDecimal::class);
        app()->bind(Currency::class, Decimal::class);
    }

    public function download($title)
    {
        return $this->excel->setFilename($title)->download('xlsx');
    }

    public function save($filename)
    {
        return $this->excel->setFilename($filename)->save();
    }

    public function init()
    {
        $name       = str_random(25);
        $this->file = Excel::create($name, function ($excel) {
            $excel->sheet('Report Sheet', function ($sheet) {
            });
        })->store('xls', false, true);
    }

    public function finalize()
    {
        unlink($this->file["full"]);
    }

    public function generate()
    {
        $this->excel = Excel::create($this->file["full"], function ($excel) {
            $excel->sheet('Report Sheet', function ($sheet) {
                $this->writeHeader($sheet);
                $rowPointer = 2;
                $this->forEachRecord(function ($newRow) use ($sheet, &$rowPointer) {
                    $this->writeRecordToSheet($rowPointer, $newRow, $sheet);
                    $rowPointer++;
                });
            });
        });
    }

    private function writeHeader($sheet)
    {
        $letter = "A";
        $this->getExportFields()->each(function ($field) use (&$letter, $sheet) {
            $this->formatExcelField($field, $letter, $sheet);
        });
        $sheet->freezeFirstRow();
        $sheet->getStyle("A1:{$letter}1")->getFont()->setBold( true );
        $sheet->cells("A1:{$letter}1", function($cells) {
            $cells->setBackground('#282223');
        });
    }

    private function formatExcelField($field, &$letter, $sheet)
    {
        if ($field->isNumeric()) {
            $sheet->setColumnFormat([$letter => "0.00"]);
            $sheet->setColumnFormat(["{$letter}1" => ""]);
        }
        $sheet->getStyle("{$letter}1")->getFont()->setColor(new PHPExcel_Style_Color("ea5b2e"));
        $sheet->setCellValue("{$letter}1", $field->getTitle());
        ++$letter;
    }

    private function writeRecordToSheet($rowPointer, $record, $sheet)
    {
        $letter = "A";
        foreach ($this->getExportFields() as $field) {
            $sheet->setCellValue($letter++ . $rowPointer, $field->getValue($record));
        }
    }

    protected function getType()
    {
        return "csv";
    }
}
