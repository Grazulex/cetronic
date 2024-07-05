<?php

declare(strict_types=1);

namespace App\Exports;

use App\Exports\Sheets\CategorySheet;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

final class ItemsExport implements FromQuery, WithMultipleSheets
{
    use Exportable;

    public function query(): Relation|Builder|Item|\Illuminate\Database\Query\Builder
    {
        return Item::query()->where('is_export', true)->with('category')->with('brand')->with('metas');
    }

    public function sheets(): array
    {
        $sheets = [];
        $categories = Category::where('is_export', true)->orderBy(column: 'name')->get();
        foreach ($categories as $category) {
            $sheets[] = new CategorySheet(category: $category);
        }

        return $sheets;
    }
}
