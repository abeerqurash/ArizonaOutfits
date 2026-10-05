<?php
namespace App\Exports;
use Maatwebsite\Excel\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Cell\{Cell,DataType};
class SafeExportValueBinder extends DefaultValueBinder
{
    public function bindValue(Cell $cell,$value): bool
    {
        if(is_string($value)){
            // Prevent spreadsheet formulas and preserve SKU leading zeroes.
            $cell->setValueExplicit($value,DataType::TYPE_STRING);
            return true;
        }
        return parent::bindValue($cell,$value);
    }
}
