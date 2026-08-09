<?php

namespace App\Exports;

use App\Models\PurchaseOrder;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class PurchaseOrderExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithColumnFormatting,
    WithStyles,
    WithTitle,
    WithEvents
{
    public function __construct(
        private readonly PurchaseOrder $purchaseOrder
    ) {
        $this->purchaseOrder->loadMissing([
            'items.product',
            'items.variant',
            'creator',
        ]);
    }

    /**
     * Purchase-order item rows.
     */
    public function collection(): Collection
    {
        return $this->purchaseOrder
            ->items
            ->values();
    }

    /**
     * Spreadsheet column headings.
     */
    public function headings(): array
    {
        return [
            'Product / Variant',
            'SKU',
            'Product Type',
            'Stock Before',
            'Reorder Point',
            'Suggested Quantity',
            'Ordered Quantity',
            'Received Quantity',
            'Remaining Quantity',
            'Unit Cost',
            'Line Total',
            'Receiving Progress',
        ];
    }

    /**
     * Convert one purchase-order item into one spreadsheet row.
     */
    public function map(
        mixed $item
    ): array {
        $orderedQuantity = max(
            0,
            (int) $item->quantity_ordered
        );

        $receivedQuantity = max(
            0,
            (int) $item->quantity_received
        );

        $remainingQuantity = max(
            0,
            $orderedQuantity
                - $receivedQuantity
        );

        $receivingPercentage =
            $orderedQuantity > 0
                ? min(
                    100,
                    (int) round(
                        (
                            $receivedQuantity
                            / $orderedQuantity
                        ) * 100
                    )
                )
                : 0;

        return [
            $item->item_name,

            $item->sku
                ?: 'Not assigned',

            $item->product_variant_id
                ? 'Variant'
                : 'Simple Product',

            (int) $item->stock_before,

            $item->reorder_point !== null
                ? (int) $item->reorder_point
                : 'Not set',

            $item->suggested_quantity !== null
                ? (int) $item->suggested_quantity
                : 'Not set',

            $orderedQuantity,

            $receivedQuantity,

            $remainingQuantity,

            (float) $item->unit_cost,

            (float) $item->line_total,

            $receivingPercentage . '%',
        ];
    }

    /**
     * Worksheet name.
     */
    public function title(): string
    {
        return 'Purchase Order';
    }

    /**
     * Currency and quantity formatting.
     */
    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_NUMBER,
            'E' => NumberFormat::FORMAT_NUMBER,
            'F' => NumberFormat::FORMAT_NUMBER,
            'G' => NumberFormat::FORMAT_NUMBER,
            'H' => NumberFormat::FORMAT_NUMBER,
            'I' => NumberFormat::FORMAT_NUMBER,
            'J' => '£#,##0.00',
            'K' => '£#,##0.00',
        ];
    }

    /**
     * Basic worksheet styling.
     */
    public function styles(
        Worksheet $sheet
    ): array {
        $sheet->freezePane('A2');

        $sheet
            ->getStyle('A1:L1')
            ->getFont()
            ->setBold(true)
            ->setSize(10)
            ->getColor()
            ->setARGB('FFFFFFFF');

        $sheet
            ->getStyle('A1:L1')
            ->getFill()
            ->setFillType(
                Fill::FILL_SOLID
            )
            ->getStartColor()
            ->setARGB('FF111827');

        $sheet
            ->getStyle('A1:L1')
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_CENTER
            )
            ->setVertical(
                Alignment::VERTICAL_CENTER
            );

        $sheet
            ->getRowDimension(1)
            ->setRowHeight(29);

        return [];
    }

    /**
     * Add purchase-order information and totals.
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class =>
                function (
                    AfterSheet $event
                ): void {
                    $sheet = $event->sheet
                        ->getDelegate();

                    $highestItemRow = max(
                        1,
                        $sheet->getHighestRow()
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | Insert purchase-order details above the table
                    |--------------------------------------------------------------------------
                    */

                    $sheet->insertNewRowBefore(
                        1,
                        13
                    );

                    $purchaseOrder =
                        $this->purchaseOrder;

                    $creatorName =
                        $purchaseOrder->creator
                            ? (
                                $purchaseOrder
                                    ->creator
                                    ->name
                                ?? $purchaseOrder
                                    ->creator
                                    ->email
                            )
                            : 'System';

                    $sheet->setCellValue(
                        'A1',
                        'Arizona Outfits'
                    );

                    $sheet->setCellValue(
                        'A2',
                        'Purchase Order'
                    );

                    $sheet->setCellValue(
                        'A3',
                        'Reference'
                    );

                    $sheet->setCellValue(
                        'B3',
                        $purchaseOrder->reference
                    );

                    $sheet->setCellValue(
                        'D3',
                        'Status'
                    );

                    $sheet->setCellValue(
                        'E3',
                        $purchaseOrder->status_label
                    );

                    $sheet->setCellValue(
                        'A4',
                        'Supplier Name'
                    );

                    $sheet->setCellValue(
                        'B4',
                        $purchaseOrder->supplier_name
                            ?: 'Not assigned'
                    );

                    $sheet->setCellValue(
                        'D4',
                        'Supplier Email'
                    );

                    $sheet->setCellValue(
                        'E4',
                        $purchaseOrder->supplier_email
                            ?: 'Not assigned'
                    );

                    $sheet->setCellValue(
                        'A5',
                        'Supplier Phone'
                    );

                    $sheet->setCellValue(
                        'B5',
                        $purchaseOrder->supplier_phone
                            ?: 'Not assigned'
                    );

                    $sheet->setCellValue(
                        'D5',
                        'Order Date'
                    );

                    $sheet->setCellValue(
                        'E5',
                        optional(
                            $purchaseOrder->order_date
                        )->format('d M Y')
                            ?: 'Not set'
                    );

                    $sheet->setCellValue(
                        'A6',
                        'Expected Delivery'
                    );

                    $sheet->setCellValue(
                        'B6',
                        optional(
                            $purchaseOrder
                                ->expected_date
                        )->format('d M Y')
                            ?: 'Not set'
                    );

                    $sheet->setCellValue(
                        'D6',
                        'Created By'
                    );

                    $sheet->setCellValue(
                        'E6',
                        $creatorName
                    );

                    $sheet->setCellValue(
                        'A7',
                        'Supplier Address'
                    );

                    $sheet->setCellValue(
                        'B7',
                        $purchaseOrder
                            ->supplier_address
                            ?: 'Not assigned'
                    );

                    $sheet->mergeCells('B7:L7');

                    $sheet->setCellValue(
                        'A9',
                        'Supplier Notes'
                    );

                    $sheet->setCellValue(
                        'B9',
                        $purchaseOrder->notes
                            ?: 'No supplier notes.'
                    );

                    $sheet->mergeCells('B9:L9');

                    $sheet->setCellValue(
                        'A10',
                        'Internal Notes'
                    );

                    $sheet->setCellValue(
                        'B10',
                        $purchaseOrder
                            ->internal_notes
                            ?: 'No internal notes.'
                    );

                    $sheet->mergeCells('B10:L10');

                    /*
                    |--------------------------------------------------------------------------
                    | Main title styling
                    |--------------------------------------------------------------------------
                    */

                    $sheet->mergeCells('A1:L1');
                    $sheet->mergeCells('A2:L2');

                    $sheet
                        ->getStyle('A1:L1')
                        ->getFont()
                        ->setBold(true)
                        ->setSize(18)
                        ->getColor()
                        ->setARGB('FF111827');

                    $sheet
                        ->getStyle('A1:L1')
                        ->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_LEFT
                        );

                    $sheet
                        ->getStyle('A2:L2')
                        ->getFont()
                        ->setBold(true)
                        ->setSize(13)
                        ->getColor()
                        ->setARGB('FF4F46E5');

                    $sheet
                        ->getStyle('A2:L2')
                        ->getAlignment()
                        ->setHorizontal(
                            Alignment::HORIZONTAL_LEFT
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | Information labels
                    |--------------------------------------------------------------------------
                    */

                    foreach (
                        [
                            'A3',
                            'D3',
                            'A4',
                            'D4',
                            'A5',
                            'D5',
                            'A6',
                            'D6',
                            'A7',
                            'A9',
                            'A10',
                        ] as $labelCell
                    ) {
                        $sheet
                            ->getStyle($labelCell)
                            ->getFont()
                            ->setBold(true)
                            ->getColor()
                            ->setARGB('FF64748B');
                    }

                    $sheet
                        ->getStyle('A3:L10')
                        ->getAlignment()
                        ->setVertical(
                            Alignment::VERTICAL_TOP
                        )
                        ->setWrapText(true);

                    $sheet
                        ->getStyle('A3:L10')
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(
                            Border::BORDER_THIN
                        )
                        ->getColor()
                        ->setARGB('FFE5E7EB');

                    /*
                    |--------------------------------------------------------------------------
                    | Table styling after inserted rows
                    |--------------------------------------------------------------------------
                    */

                    $headingRow = 14;

                    $finalItemRow =
                        $highestItemRow + 13;

                    $sheet
                        ->getStyle(
                            'A' . $headingRow
                            . ':L' . $headingRow
                        )
                        ->getFont()
                        ->setBold(true)
                        ->getColor()
                        ->setARGB('FFFFFFFF');

                    $sheet
                        ->getStyle(
                            'A' . $headingRow
                            . ':L' . $headingRow
                        )
                        ->getFill()
                        ->setFillType(
                            Fill::FILL_SOLID
                        )
                        ->getStartColor()
                        ->setARGB('FF111827');

                    $sheet
                        ->getStyle(
                            'A' . $headingRow
                            . ':L' . $finalItemRow
                        )
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(
                            Border::BORDER_THIN
                        )
                        ->getColor()
                        ->setARGB('FFE5E7EB');

                    $sheet->setAutoFilter(
                        'A' . $headingRow
                        . ':L' . $finalItemRow
                    );

                    $sheet->freezePane(
                        'A' . ($headingRow + 1)
                    );

                    if (
                        $finalItemRow
                        > $headingRow
                    ) {
                        for (
                            $row = $headingRow + 1;
                            $row <= $finalItemRow;
                            $row++
                        ) {
                            if ($row % 2 === 0) {
                                $sheet
                                    ->getStyle(
                                        'A' . $row
                                        . ':L' . $row
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

                    /*
                    |--------------------------------------------------------------------------
                    | Totals section
                    |--------------------------------------------------------------------------
                    */

                    $totalsStartRow =
                        $finalItemRow + 3;

                    $sheet->setCellValue(
                        'I' . $totalsStartRow,
                        'Subtotal'
                    );

                    $sheet->setCellValue(
                        'K' . $totalsStartRow,
                        (float) $purchaseOrder
                            ->subtotal
                    );

                    $sheet->setCellValue(
                        'I' . (
                            $totalsStartRow + 1
                        ),
                        'Tax'
                    );

                    $sheet->setCellValue(
                        'K' . (
                            $totalsStartRow + 1
                        ),
                        (float) $purchaseOrder
                            ->tax_amount
                    );

                    $sheet->setCellValue(
                        'I' . (
                            $totalsStartRow + 2
                        ),
                        'Shipping'
                    );

                    $sheet->setCellValue(
                        'K' . (
                            $totalsStartRow + 2
                        ),
                        (float) $purchaseOrder
                            ->shipping_amount
                    );

                    $sheet->setCellValue(
                        'I' . (
                            $totalsStartRow + 3
                        ),
                        'Discount'
                    );

                    $sheet->setCellValue(
                        'K' . (
                            $totalsStartRow + 3
                        ),
                        -1 * (
                            (float) $purchaseOrder
                                ->discount_amount
                        )
                    );

                    $sheet->setCellValue(
                        'I' . (
                            $totalsStartRow + 4
                        ),
                        'Grand Total'
                    );

                    $sheet->setCellValue(
                        'K' . (
                            $totalsStartRow + 4
                        ),
                        (float) $purchaseOrder
                            ->total_amount
                    );

                    $sheet->mergeCells(
                        'I' . $totalsStartRow
                        . ':J' . $totalsStartRow
                    );

                    $sheet->mergeCells(
                        'I' . (
                            $totalsStartRow + 1
                        )
                        . ':J' . (
                            $totalsStartRow + 1
                        )
                    );

                    $sheet->mergeCells(
                        'I' . (
                            $totalsStartRow + 2
                        )
                        . ':J' . (
                            $totalsStartRow + 2
                        )
                    );

                    $sheet->mergeCells(
                        'I' . (
                            $totalsStartRow + 3
                        )
                        . ':J' . (
                            $totalsStartRow + 3
                        )
                    );

                    $sheet->mergeCells(
                        'I' . (
                            $totalsStartRow + 4
                        )
                        . ':J' . (
                            $totalsStartRow + 4
                        )
                    );

                    $sheet->mergeCells(
                        'K' . $totalsStartRow
                        . ':L' . $totalsStartRow
                    );

                    $sheet->mergeCells(
                        'K' . (
                            $totalsStartRow + 1
                        )
                        . ':L' . (
                            $totalsStartRow + 1
                        )
                    );

                    $sheet->mergeCells(
                        'K' . (
                            $totalsStartRow + 2
                        )
                        . ':L' . (
                            $totalsStartRow + 2
                        )
                    );

                    $sheet->mergeCells(
                        'K' . (
                            $totalsStartRow + 3
                        )
                        . ':L' . (
                            $totalsStartRow + 3
                        )
                    );

                    $sheet->mergeCells(
                        'K' . (
                            $totalsStartRow + 4
                        )
                        . ':L' . (
                            $totalsStartRow + 4
                        )
                    );

                    $sheet
                        ->getStyle(
                            'I' . $totalsStartRow
                            . ':L' . (
                                $totalsStartRow + 4
                            )
                        )
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(
                            Border::BORDER_THIN
                        )
                        ->getColor()
                        ->setARGB('FFE5E7EB');

                    $sheet
                        ->getStyle(
                            'I' . $totalsStartRow
                            . ':J' . (
                                $totalsStartRow + 4
                            )
                        )
                        ->getFont()
                        ->setBold(true);

                    $sheet
                        ->getStyle(
                            'K' . $totalsStartRow
                            . ':L' . (
                                $totalsStartRow + 4
                            )
                        )
                        ->getNumberFormat()
                        ->setFormatCode(
                            '£#,##0.00'
                        );

                    $sheet
                        ->getStyle(
                            'I' . (
                                $totalsStartRow + 4
                            )
                            . ':L' . (
                                $totalsStartRow + 4
                            )
                        )
                        ->getFont()
                        ->setBold(true)
                        ->setSize(12)
                        ->getColor()
                        ->setARGB('FF15803D');

                    /*
                    |--------------------------------------------------------------------------
                    | Column widths
                    |--------------------------------------------------------------------------
                    */

                    $sheet
                        ->getColumnDimension('A')
                        ->setWidth(34);

                    $sheet
                        ->getColumnDimension('B')
                        ->setWidth(18);

                    $sheet
                        ->getColumnDimension('C')
                        ->setWidth(17);

                    foreach (
                        [
                            'D',
                            'E',
                            'F',
                            'G',
                            'H',
                            'I',
                        ] as $column
                    ) {
                        $sheet
                            ->getColumnDimension(
                                $column
                            )
                            ->setWidth(16);
                    }

                    $sheet
                        ->getColumnDimension('J')
                        ->setWidth(15);

                    $sheet
                        ->getColumnDimension('K')
                        ->setWidth(17);

                    $sheet
                        ->getColumnDimension('L')
                        ->setWidth(18);

                    /*
                    |--------------------------------------------------------------------------
                    | Page setup
                    |--------------------------------------------------------------------------
                    */

                    $sheet
                        ->getPageSetup()
                        ->setOrientation(
                            PageSetup::ORIENTATION_LANDSCAPE
                        );

                    $sheet
                        ->getPageSetup()
                        ->setPaperSize(
                            PageSetup::PAPERSIZE_A4
                        );

                    $sheet
                        ->getPageSetup()
                        ->setFitToWidth(1);

                    $sheet
                        ->getPageSetup()
                        ->setFitToHeight(0);
                },
        ];
    }
}