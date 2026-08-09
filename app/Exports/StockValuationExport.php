<?php

namespace App\Exports;

use App\Models\Product;
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

class StockValuationExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithColumnFormatting,
    WithStyles,
    WithTitle
{
    /**
     * Products supplied by the controller.
     *
     * This can be used later when exporting only filtered products.
     */
    private ?Collection $products;

    /**
     * Create a new Stock Valuation export.
     */
    public function __construct(
        ?Collection $products = null
    ) {
        $this->products = $products;
    }

    /**
     * Return all products used in the Excel report.
     */
    public function collection(): Collection
    {
        /*
         * When the controller passes filtered or already-calculated products,
         * use that collection directly.
         */
        if ($this->products instanceof Collection) {
            return $this->prepareProducts(
                $this->products
            );
        }

        /*
         * Normal export without filters.
         */
        $products = Product::query()
            ->with('categories')
            ->orderBy('title')
            ->get();

        return $this->prepareProducts(
            $products
        );
    }

    /**
     * Calculate valuation fields for every product.
     */
    private function prepareProducts(
        Collection $products
    ): Collection {
        foreach ($products as $product) {
            $stock = max(
                0,
                (int) ($product->stock ?? 0)
            );

            $costPrice = max(
                0,
                (float) ($product->cost_price ?? 0)
            );

            $regularPrice = max(
                0,
                (float) ($product->regular_price ?? 0)
            );

            $salePrice = $product->sale_price !== null
                ? (float) $product->sale_price
                : null;

            /*
             * Use sale price only when it is valid and lower
             * than the regular price.
             */
            $sellingPrice =
                $salePrice !== null
                && $salePrice > 0
                && $salePrice < $regularPrice
                    ? $salePrice
                    : $regularPrice;

            $inventoryCost =
                $costPrice * $stock;

            $inventoryRetail =
                $sellingPrice * $stock;

            $inventoryProfit =
                ($sellingPrice - $costPrice)
                * $stock;

            $margin = $sellingPrice > 0
                ? (
                    (
                        $sellingPrice - $costPrice
                    ) / $sellingPrice
                ) * 100
                : 0;

            /*
             * Store calculated values temporarily on the
             * Product model instance.
             */
            $product->calculated_selling_price =
                round($sellingPrice, 2);

            $product->calculated_inventory_cost =
                round($inventoryCost, 2);

            $product->calculated_inventory_retail =
                round($inventoryRetail, 2);

            $product->calculated_inventory_profit =
                round($inventoryProfit, 2);

            $product->calculated_margin =
                round($margin, 2);
        }

        return $products;
    }

    /**
     * Excel worksheet column headings.
     */
    public function headings(): array
    {
        return [
            'Product',
            'SKU',
            'Categories',
            'Stock Status',
            'Stock Units',
            'Cost Price',
            'Selling Price',
            'Inventory Cost',
            'Retail Value',
            'Expected Profit',
            'Margin',
        ];
    }

    /**
     * Convert one product into one Excel row.
     */
    public function map(
        mixed $product
    ): array {
        $stock = max(
            0,
            (int) ($product->stock ?? 0)
        );

        if ($stock <= 0) {
            $stockStatus = 'Out of stock';
        } elseif ($stock <= 5) {
            $stockStatus = 'Low stock';
        } else {
            $stockStatus = 'In stock';
        }

        /*
         * Product ecommerce categories are loaded through:
         *
         * Product::categories()
         *
         * This uses App\Models\ProductCategory, not the blog
         * App\Models\Category model.
         */
        $categoryNames = $product
            ->categories
            ->pluck('title')
            ->filter()
            ->implode(', ');

        return [
            (string) ($product->title ?? ''),

            filled($product->sku)
                ? (string) $product->sku
                : 'Not assigned',

            $categoryNames !== ''
                ? $categoryNames
                : 'Uncategorised',

            $stockStatus,

            $stock,

            (float) ($product->cost_price ?? 0),

            (float) (
                $product->calculated_selling_price
                ?? $product->selling_price
                ?? 0
            ),

            (float) (
                $product->calculated_inventory_cost
                ?? $product->inventory_cost
                ?? 0
            ),

            (float) (
                $product->calculated_inventory_retail
                ?? $product->inventory_retail
                ?? 0
            ),

            (float) (
                $product->calculated_inventory_profit
                ?? $product->inventory_profit
                ?? 0
            ),

            /*
             * Excel percentage values must be stored as decimals.
             *
             * Example:
             * 25% must be stored as 0.25.
             */
            (float) (
                $product->calculated_margin
                ?? $product->margin
                ?? 0
            ) / 100,
        ];
    }

    /**
     * Excel worksheet title.
     */
    public function title(): string
    {
        return 'Stock Valuation';
    }

    /**
     * Apply Excel number formatting.
     */
    public function columnFormats(): array
    {
        return [
            'E' => NumberFormat::FORMAT_NUMBER,

            'F' => '£#,##0.00',

            'G' => '£#,##0.00',

            'H' => '£#,##0.00',

            'I' => '£#,##0.00',

            'J' => '£#,##0.00;[Red]-£#,##0.00',

            'K' => NumberFormat::FORMAT_PERCENTAGE_00,
        ];
    }

    /**
     * Apply professional worksheet styling.
     */
    public function styles(
        Worksheet $sheet
    ): array {
        $highestRow = max(
            1,
            $sheet->getHighestRow()
        );

        /*
        |--------------------------------------------------------------------------
        | Freeze header row
        |--------------------------------------------------------------------------
        */

        $sheet->freezePane('A2');

        /*
        |--------------------------------------------------------------------------
        | Enable table filters
        |--------------------------------------------------------------------------
        */

        $sheet->setAutoFilter(
            'A1:K' . $highestRow
        );

        /*
        |--------------------------------------------------------------------------
        | Header styling
        |--------------------------------------------------------------------------
        */

        $sheet
            ->getStyle('A1:K1')
            ->getFont()
            ->setBold(true)
            ->setSize(11)
            ->getColor()
            ->setARGB('FFFFFFFF');

        $sheet
            ->getStyle('A1:K1')
            ->getFill()
            ->setFillType(
                Fill::FILL_SOLID
            )
            ->getStartColor()
            ->setARGB('FF111827');

        $sheet
            ->getStyle('A1:K1')
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

        /*
        |--------------------------------------------------------------------------
        | Complete worksheet borders
        |--------------------------------------------------------------------------
        */

        $sheet
            ->getStyle(
                'A1:K' . $highestRow
            )
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(
                Border::BORDER_THIN
            )
            ->getColor()
            ->setARGB('FFE5E7EB');

        /*
        |--------------------------------------------------------------------------
        | Row vertical alignment
        |--------------------------------------------------------------------------
        */

        if ($highestRow >= 2) {
            $sheet
                ->getStyle(
                    'A2:K' . $highestRow
                )
                ->getAlignment()
                ->setVertical(
                    Alignment::VERTICAL_CENTER
                );

            /*
             * Text columns.
             */
            $sheet
                ->getStyle(
                    'A2:D' . $highestRow
                )
                ->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_LEFT
                );

            /*
             * Numeric columns.
             */
            $sheet
                ->getStyle(
                    'E2:K' . $highestRow
                )
                ->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_RIGHT
                );

            /*
             * Allow categories and product names to wrap.
             */
            $sheet
                ->getStyle(
                    'A2:C' . $highestRow
                )
                ->getAlignment()
                ->setWrapText(true);

            /*
             * Add alternating row backgrounds.
             */
            for (
                $row = 2;
                $row <= $highestRow;
                $row++
            ) {
                if ($row % 2 === 0) {
                    $sheet
                        ->getStyle(
                            'A' . $row
                            . ':K' . $row
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
        | Custom column widths
        |--------------------------------------------------------------------------
        |
        | ShouldAutoSize is enabled, but these minimum widths make
        | long titles and category names easier to read.
        |
        */

        $sheet
            ->getColumnDimension('A')
            ->setWidth(30);

        $sheet
            ->getColumnDimension('B')
            ->setWidth(17);

        $sheet
            ->getColumnDimension('C')
            ->setWidth(28);

        $sheet
            ->getColumnDimension('D')
            ->setWidth(16);

        $sheet
            ->getColumnDimension('E')
            ->setWidth(13);

        $sheet
            ->getColumnDimension('F')
            ->setWidth(15);

        $sheet
            ->getColumnDimension('G')
            ->setWidth(15);

        $sheet
            ->getColumnDimension('H')
            ->setWidth(17);

        $sheet
            ->getColumnDimension('I')
            ->setWidth(17);

        $sheet
            ->getColumnDimension('J')
            ->setWidth(18);

        $sheet
            ->getColumnDimension('K')
            ->setWidth(13);

        /*
        |--------------------------------------------------------------------------
        | Print setup
        |--------------------------------------------------------------------------
        */

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

        $sheet
            ->getPageMargins()
            ->setTop(0.4);

        $sheet
            ->getPageMargins()
            ->setBottom(0.4);

        $sheet
            ->getPageMargins()
            ->setLeft(0.35);

        $sheet
            ->getPageMargins()
            ->setRight(0.35);

        return [];
    }
}