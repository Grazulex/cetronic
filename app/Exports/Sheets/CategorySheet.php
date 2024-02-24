<?php

declare(strict_types=1);

namespace App\Exports\Sheets;

use App\Models\Category;
use App\Models\Item;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;

final class CategorySheet implements FromArray, WithTitle
{
    public function __construct(private Category $category)
    {
    }

    public function array(): array
    {
        $items = Item::join('brands', 'items.brand_id', '=', 'brands.id')->withoutTrashed()->where('category_id', $this->category->id)->orderBy('brands.name')->orderBy('reference')->select('items.*')->get();
        $metas = $this->category->metas()->where('is_export', true)->orderBy('name')->pluck('name')->toArray();
        $metasId = $this->category->metas()->where('is_export', true)->orderBy('name')->get();
        $content[] = array_merge(['reference', 'marque'], $metas);
        foreach ($items as $item) {
            if ( ! $item->is_published) {
                continue;
            }
            $itemMetas = [];
            foreach ($metasId as $metaId) {
                $itemMeta = $item->metas()->where('meta_id', $metaId->id)->first();
                if ($itemMeta) {
                    $itemMetas[] = $itemMeta->value;
                } else {
                    $itemMetas[] = '';
                }
            }
            $content[] = array_merge([$item->reference, $item->brand->name], $itemMetas);
        }

        return $content;
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->category->name;
    }
}
