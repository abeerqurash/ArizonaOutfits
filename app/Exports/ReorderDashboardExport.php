<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ReorderDashboardExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithColumnFormatting,
    WithStyles,
    WithTitle
{
    public function __construct(
        private readonly Collection $items
    ) {
    }

    public function collection(): Collection
    {
        return $this->items;
    }

    public function headings(): array
    {
        return [
            'Product / Variant',
            'SKU',
            'Type',
            'Categories',
            'Stock Status',
            'Current Stock',
            'Reorder Point',
            'Configured Reorder Quantity',
            'Suggested Purchase Quantity',
            'Cost Price',
            'Estimated Purchase Cost',
            'Urgency',
            'Missing Reorder Point',
            'Missing Reorder Quantity',
            'Missing Cost Price',
        ];
    }

    public function map(
        mixed $item
    ): array {
        $categoryNames = $item['categories']
            ->pluck('title')
            ->filter()
            ->implode(', ');

        return [
            $item['item_name'],

            $item['sku']
                ?: 'Not assigned',

            $item['is_variant']
                ? 'Variant'
                : 'Simple Product',

            $categoryNames
                ?: 'Uncategorised',

            $item['stock_status_label'],

            (int) $item['stock'],

            $item['missing_reorder_point']
                ? 'Not set'
                : (int) $item['reorder_point'],

            $item['missing_reorder_quantity']
                ? 'Not set'
                : (int) $item['reorder_quantity'],

            (int) $item['suggested_quantity'],

            $item['missing_cost']
                ? null
                : (float) $item['cost_price'],

            (float) $item['estimated_cost'],

            $item['urgency_label'],

            $item['missing_reorder_point']
                ? 'Yes'
                : 'No',

            $item['missing_reorder_quantity']
                ? 'Yes'
                : 'No',

            $item['missing_cost']
                ? 'Yes'
                : 'No',
        ];
    }

    public function title(): string
    {
        return 'Reorder Purchase List';
    }

    public function columnFormats(): array
    {
        return [
            'F' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_NUMBER,
            'H' => NumberFormat::FORMAT_NUMBER,
            'I' => NumberFormat::FORMAT_NUMBER,
            'J' => '£#,##0.00',
            'K' => '£#,##0.00',
        ];
    }

    public function styles(
        Worksheet $sheet
    ): array {
        $highestRow = max(
            1,
            $sheet->getHighestRow()
        );

        $sheet->freezePane('A2');

        $sheet->setAutoFilter(
            'A1:O' . $highestRow
        );

        $sheet
            ->getStyle('A1:O1')
            ->getFont()
            ->setBold(true)
            ->setSize(11)
            ->getColor()
            ->setARGB('FFFFFFFF');

        $sheet
            ->getStyle('A1:O1')
            ->getFill()
            ->setFillType(
                Fill::FILL_SOLID
            )
            ->getStartColor()
            ->setARGB('FF111827');

        $sheet
            ->getStyle('A1:O1')
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_CENTER
            )
            ->setVertical(
                Alignment::VERTICAL_CENTER
            );

        $sheet
            ->getRowDimension(1)
            ->setRowHeight(30);

        $sheet
            ->getStyle(
                'A1:O' . $highestRow
            )
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(
                Border::BORDER_THIN
            )
            ->getColor()
            ->setARGB('FFE5E7EB');

        if ($highestRow >= 2) {
            $sheet
                ->getStyle(
                    'A2:O' . $highestRow
                )
                ->getAlignment()
                ->setVertical(
                    Alignment::VERTICAL_CENTER
                );

            $sheet
                ->getStyle(
                    'A2:E' . $highestRow
                )
                ->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_LEFT
                );

            $sheet
                ->getStyle(
                    'F2:K' . $highestRow
                )
                ->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_RIGHT
                );

            $sheet
                ->getStyle(
                    'L2:O' . $highestRow
                )
                ->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_CENTER
                );

            $sheet
                ->getStyle(
                    'A2:D' . $highestRow
                )
                ->getAlignment()
                ->setWrapText(true);

            for (
                $row = 2;
                $row <= $highestRow;
                $row++
            ) {
                if ($row % 2 === 0) {
                    $sheet
                        ->getStyle(
                            'A' . $row
                            . ':O' . $row
                        )
                        ->getFill()
                        ->setFillType(
                            Fill::FILL_SOLID
                        )
                        ->getStartColor()
                        ->setARGB('FFF8FAFC');
                }

                $sheet
                    ->getRowDimension($row)
                    ->setRowHeight(22);
            }
        }

        $sheet
            ->getColumnDimension('A')
            ->setWidth(34);

        $sheet
            ->getColumnDimension('B')
            ->setWidth(18);

        $sheet
            ->getColumnDimension('C')
            ->setWidth(16);

        $sheet
            ->getColumnDimension('D')
            ->setWidth(28);

        $sheet
            ->getColumnDimension('E')
            ->setWidth(16);

        $sheet
            ->getColumnDimension('F')
            ->setWidth(14);

        $sheet
            ->getColumnDimension('G')
            ->setWidth(14);

        $sheet
            ->getColumnDimension('H')
            ->setWidth(21);

        $sheet
            ->getColumnDimension('I')
            ->setWidth(22);

        $sheet
            ->getColumnDimension('J')
            ->setWidth(15);

        $sheet
            ->getColumnDimension('K')
            ->setWidth(20);

        $sheet
            ->getColumnDimension('L')
            ->setWidth(14);

        $sheet
            ->getColumnDimension('M')
            ->setWidth(20);

        $sheet
            ->getColumnDimension('N')
            ->setWidth(23);

        $sheet
            ->getColumnDimension('O')
            ->setWidth(18);

        $sheet
            ->getPageSetup()
            ->setOrientation(
                \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
            );

        $sheet
            ->getPageSetup()
            ->setPaperSize(
                \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4
            );

        $sheet
            ->getPageSetup()
            ->setFitToWidth(1);

        $sheet
            ->getPageSetup()
            ->setFitToHeight(0);

        return [];
    }
}