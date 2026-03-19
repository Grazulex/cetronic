<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryMeta;
use App\Models\Item;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

final class CategoriesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    private ?Collection $categoryMetas = null;

    public function __construct(private readonly Category $category, private readonly ?Brand $brand = null, private readonly bool $includeDeleted = false) {}

    public function query()
    {
        $query = Item::query()
            ->where('category_id', $this->category->id)
            ->when($this->includeDeleted, fn($q) => $q->withTrashed(), fn($q) => $q->withoutTrashed())
            ->with(['category', 'brand', 'master', 'metas']);

        if ($this->brand) {
            $query->where('brand_id', $this->brand->id);
        }

        return $query;
    }

    public function headings(): array
    {
        $headers = [
            '#',
            'Reference',
            'Category',
            'Brand',
            'Master Reference',
            'Description',
            'Is New',
            'Is Best Seller',
            'Is Published',
            '!!  DELETED !!',
            ...($this->includeDeleted ? ['Is Deleted'] : []),
            'Price',
            'Price B2B',
            'Price Promo',
            'Price Special 1',
            'Price Special 2',
            'Price Special 3',
            'Sale Price',
            'Price Fix',
            'Reseller Price',
            'Multiple Quantity',
            'catalog_group',
        ];

        foreach ($this->getCategoryMetas() as $meta) {
            $headers[] = $meta->name;
        }

        return $headers;
    }

    public function map($row): array
    {
        $data = [
            $row->id,
            $row->reference,
            $row->category->name,
            $row->brand->name,
            $row->master?->reference ?? '',
            $row->description,
            $row->is_new ? '1' : '0',
            $row->is_best_seller ? '1' : '0',
            $row->is_published ? '1' : '0',
            '',
            ...($this->includeDeleted ? [$row->deleted_at !== null ? 'y' : 'n'] : []),
            $row->price,
            $row->price_b2b,
            $row->price_promo,
            $row->price_special1,
            $row->price_special2,
            $row->price_special3,
            $row->sale_price,
            $row->price_fix,
            $row->reseller_price,
            $row->multiple_quantity,
            $row->catalog_group,
        ];

        $metaValues = $row->metas->keyBy('meta_id');

        foreach ($this->getCategoryMetas() as $meta) {
            $data[] = $metaValues->get($meta->id)?->value ?? '';
        }

        return $data;
    }

    private function getCategoryMetas(): Collection
    {
        if ($this->categoryMetas === null) {
            $this->categoryMetas = CategoryMeta::where('category_id', $this->category->id)->get();
        }

        return $this->categoryMetas;
    }
}
