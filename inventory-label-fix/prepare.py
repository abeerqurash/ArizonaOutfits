from pathlib import Path
R=Path(r'C:\xampp\htdocs\ArizonaOutfits');O=R/'inventory-label-fix'
s=(R/'app/Services/InventoryCatalogService.php').read_text(encoding='utf-8-sig')
needle='    /** Read-only projections: never save these copies of catalog products. */'
formatter='''    /** Support stored option records and legacy name/value maps. */
    public static function variantLabel(mixed $options): string
    {
        if (is_string($options)) $options = json_decode($options, true);
        if (!is_array($options)) return '';
        $labels = [];
        foreach ($options as $key => $option) {
            if (is_array($option)) {
                $name = $option['option_name'] ?? $option['name'] ?? (is_string($key) ? $key : '');
                $value = $option['value_label'] ?? $option['label'] ?? $option['value'] ?? null;
            } else {
                $name = is_string($key) ? $key : '';
                $value = $option;
            }
            if (!is_scalar($value) || trim((string) $value) === '') continue;
            $name = is_scalar($name) ? trim((string) $name) : '';
            $labels[] = ($name !== '' ? ucfirst($name) . ': ' : '') . trim((string) $value);
        }
        return implode(', ', $labels);
    }

'''
assert needle in s;s=s.replace(needle,formatter+needle)
old="collect($entity->options ?? [])->map(fn ($value, $key) => is_scalar($value) ? $key . ': ' . $value : json_encode($value))->implode(', ')"
assert old in s;s=s.replace(old,'self::variantLabel($entity->options)')
(O/'01_InventoryCatalogService.php.txt').write_text(s,encoding='utf-8')
(O/'InventoryCatalogService.php').write_text(s,encoding='utf-8')
p='app/Http/Controllers/Admin/InventoryReportController.php';s=(R/p).read_text(encoding='utf-8-sig')
old="collect($row->variant->options ?? [])->map(fn ($value, $key) => is_scalar($value) ? $key . ': ' . $value : json_encode($value))->implode(', ')"
assert old in s;s=s.replace(old,'\\App\\Services\\InventoryCatalogService::variantLabel($row->variant->options)')
(O/'02_InventoryReportController.php.txt').write_text(s,encoding='utf-8')
(O/'InventoryReportController.php').write_text(s,encoding='utf-8')
(O/'00_INSTRUCTIONS.txt').write_text('''VARIANT LABEL DISPLAY CORRECTION
Replace complete file contents using these two TXT files:

01_InventoryCatalogService.php.txt
Destination: C:\\xampp\\htdocs\\ArizonaOutfits\\app\\Services\\InventoryCatalogService.php

02_InventoryReportController.php.txt
Destination: C:\\xampp\\htdocs\\ArizonaOutfits\\app\\Http\\Controllers\\Admin\\InventoryReportController.php

Copy all TXT contents into the destination PHP file, replacing all old contents.
Save as UTF-8. Do not keep .txt on the destination file.

Run in PowerShell:
Set-Location 'C:\\xampp\\htdocs\\ArizonaOutfits'
& 'C:\\xampp\\php\\php.exe' artisan optimize:clear

Refresh Inventory Reports with Ctrl+F5. Example:
test Product 2 — Color: Red, Material: Leather

The catalog correction also improves variant labels on Stock Valuation.
The controller correction fixes the Variant column in report CSV exports.
No migration or database changes are needed. Stock quantities are unchanged.
''',encoding='utf-8')
print('Two complete TXT replacements prepared; installed source unchanged.')
