<?php

declare(strict_types=1);

namespace App\Exports\BrandDiscounts;

use App\Models\Brand;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

final class BrandDiscountsExport implements WithMultipleSheets
{
    public function __construct(public readonly Brand $brand) {}

    public function fileName(): string
    {
        return 'Remises_' . Str::slug($this->brand->name) . '_' . Carbon::now()->format('Y-m-d_His') . '.xlsx';
    }

    /**
     * @return array<int, BrandDiscountsSheet|BrandDiscountsLegendSheet>
     */
    public function sheets(): array
    {
        return [
            new BrandDiscountsSheet($this->brand),
            new BrandDiscountsLegendSheet(),
        ];
    }
}
