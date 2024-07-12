<?php

declare(strict_types=1);

namespace App\Exports;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryMeta;
use App\Models\Item;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

final class CategoriesExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize
{
    use Exportable;

    public function __construct(private readonly Category $category, private readonly ?Brand $brand = null) {}


    public function query()
    {
        if ($this->brand) {
            return Item::query()
                ->where('category_id', $this->category->id)
                ->where('brand_id', $this->brand->id)
                ->withoutTrashed()
                ->with('category')
                ->with('brand')
                ->with('metas');
        }

        return Item::query()
            ->where('category_id', $this->category->id)
            ->withoutTrashed()
            ->with('category')
            ->with('brand')
            ->with('metas');
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
            'Is Published',
            '!!  DELETED !!',
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

        $metas = CategoryMeta::where('category_id', $this->category->id)->get();
        foreach ($metas as $meta) {
            $headers[] = $meta->name;
        }


        return $headers;
    }


    public function map($row): array
    {
        $data = [$row->id, $row->reference, $row->category->name, $row->brand->name, ($row->master) ? $row->master->reference : '', $row->description, ($row->is_new) ? '1' : '0', ($row->is_published) ? '1' : '0', '', $row->price, $row->price_b2b, $row->price_promo, $row->price_special1, $row->price_special2, $row->price_special3, $row->sale_price, $row->price_fix, $row->reseller_prive, $row->multiple_quantity, $row->catalog_group];

        $metas = CategoryMeta::where('category_id', $this->category->id)->get();
        foreach ($metas as $meta) {
            $data[] = ($row->metas()->where('meta_id', $meta->id)->first()) ? $row->metas()->where('meta_id', $meta->id)->first()->value : '';
        }

        return $data;
    }
}
