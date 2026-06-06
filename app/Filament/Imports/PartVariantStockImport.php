<?php

namespace App\Filament\Imports;

use App\Models\PartVariant;
use Illuminate\Support\Collection;

class PartVariantStockImport
{
    public function import(string $filePath): array
    {
        $reader = \OpenSpout\Reader\XLSX\Reader::createFromFile($filePath);
        $reader->open($filePath);

        $updated = 0;
        $skipped = 0;
        $errors = [];

        foreach ($reader->getSheetIterator() as $sheet) {
            $rowIndex = 0;
            foreach ($sheet->getRowIterator() as $row) {
                $rowIndex++;
                // Skip header row
                if ($rowIndex === 1) continue;

                $cells = $row->getCells();
                $sku = trim($cells[0]->getValue() ?? '');
                $stockQty = (int) trim($cells[1]->getValue() ?? '0');

                if (empty($sku)) {
                    $skipped++;
                    continue;
                }

                $variant = PartVariant::where('sku', $sku)->first();

                if (!$variant) {
                    $errors[] = "Row {$rowIndex}: SKU '{$sku}' not found.";
                    $skipped++;
                    continue;
                }

                $variant->stock = max(0, $stockQty);
                $variant->stock_updated_at = now();
                $variant->save();
                $updated++;
            }
        }

        $reader->close();

        return [
            'updated' => $updated,
            'skipped' => $skipped,
            'errors' => $errors,
        ];
    }
}
