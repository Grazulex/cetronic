<?php

declare(strict_types=1);

namespace App\Imports;

use App\Models\Brand;
use App\Models\CategoryMeta;
use App\Models\Item;
use App\Models\ItemMeta;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

final class ItemsImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public function __construct(private readonly int $category_id, private array $headers) {}

    public function collection(Collection $collection): void
    {
        foreach (array_filter($this->headers[0][0]) as $key => $value) {
            if (empty(trim($value))) {
                unset($this->headers[0][0][$key]);
            }
            if ($value === $key) {
                unset($this->headers[0][0][$key]);
            }
        }

        $remove = [
            '',
            'reference',
            'category',
            'brand',
            'master_reference',
            'description',
            'is_new',
            'is_published',
            'deleted',
            'price',
            'price_b2b',
            'price_promo',
            'price_special_1',
            'price_special_2',
            'price_special_3',
            'sale_price',
            'price_fix',
            'reseller_price',
            'multiple_quantity',
            'catalog_group',
        ];

        $metas = array_diff($this->headers[0][0], $remove);

        foreach ($metas as $meta) {
            $meta = str_replace('_', ' ', $meta);
            CategoryMeta::firstOrCreate(
                [
                    'name' => trim(mb_convert_encoding($meta, 'UTF-8', 'UTF-8')),
                    'category_id' => $this->category_id,
                ],
                [
                    'name' => trim(mb_convert_encoding($meta, 'UTF-8', 'UTF-8')),
                ],
            );
        }

        foreach ($collection as $row) {
            if ( ! isset($row['brand'])) {
                if (2 === $this->category_id) {
                    $brand = 'Watch Straps';
                } else {
                    $brand = 'Others';
                }
            } else {
                $brand = $row['brand'];
            }

            $brand = Brand::firstOrCreate(
                ['name' => trim($row['brand'])],
                [
                    'name' => trim($row['brand']),
                    'description' => '',
                ],
            );


            if ( ! isset($row['description'])) {
                $description = $row['reference'];
            } else {
                $description = $row['description'];
            }

            if (isset($row['colisage'])) {
                $multi = 1;
                $parts = explode(
                    '/',
                    str_replace('"', '', $row['colisage']),
                );
                if (count($parts) > 0) {
                    $multi = (int) ($parts[0]);
                    if (0 === $multi) {
                        $multi = 1;
                    }
                }
            } else {
                $multi = $row['multiple_quantity'];
            }

            $item = Item::updateOrCreate(
                ['reference' => $row['reference']],
                [
                    'description' => $description,
                    'brand_id' => $brand->id,
                    'category_id' => $this->category_id,
                    'master_id' => Item::where('reference', $row['master_reference'])->first()->id ?? null,
                    'is_published' => 1 === $row['is_published'],
                    'is_new' => 1 === $row['is_new'],
                    'price' => (float) ($row['price']),
                    'price_b2b' => (float) ($row['price_b2b']),
                    'price_fix' => (float) ($row['price_fix']),
                    'price_promo' => (float) ($row['price_promo']),
                    'price_special1' => (float) ($row['price_special_1']),
                    'price_special2' => (float) ($row['price_special_2']),
                    'price_special3' => (float) ($row['price_special_3']),
                    'sale_price' => (float) ($row['sale_price']),
                    'reseller_price' => (float) ($row['reseller_price']),
                    'multiple_quantity' => $multi,
                    'catalog_group' => ($row['catalog_group']) ?: null,
                ],
            );

            $this->createMetas($metas, $item, $row);
        }
    }

    private function createMetas(array $metas, Item $item, Collection $row): void
    {
        foreach ($metas as $meta) {
            $category_meta = CategoryMeta::where('name', str_replace('_', ' ', $meta))
                ->where('category_id', $this->category_id)
                ->first();

            if ($row[$meta] && str_contains((string) $row[$meta], ',')) {
                $values = explode(',', $row[$meta]);

                ItemMeta::where('item_id', $item->id)
                    ->where('meta_id', $category_meta->id)
                    ->whereNotIn('value', $values)
                    ->delete();

                foreach ($values as $value) {
                    ItemMeta::updateOrCreate(
                        [
                            'item_id' => $item->id,
                            'meta_id' => $category_meta->id,
                            'value' => trim($value),
                        ],
                        [
                            'value' => trim($value),
                        ],
                    );
                }
            } else {
                if ($row[$meta]) {

                    ItemMeta::where('item_id', $item->id)
                        ->where('meta_id', $category_meta->id)
                        ->where('value', '!=', trim((string) $row[$meta]))
                        ->delete();

                    ItemMeta::updateOrCreate(
                        [
                            'item_id' => $item->id,
                            'meta_id' => $category_meta->id,
                            'value' => trim((string) $row[$meta]),
                        ],
                        [
                            'value' => trim((string) $row[$meta]),
                        ],
                    );
                }
            }
        }
    }
}
