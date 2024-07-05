<?php

declare(strict_types=1);

namespace App\View\Components\Brands;

use App\Models\Brand;
use App\Services\BrandService;
use Closure;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\View\Component;

final class PictureComponent extends Component
{
    public function __construct(public Brand $brand) {}

    public function render(): View|Application|Factory|Htmlable|Closure|string|\Illuminate\Contracts\Foundation\Application
    {
        $brandService = new BrandService();

        $picture = $brandService->getDefaultPicture(brand: $this->brand);

        return view('components.brands.picture-component', ['picture' => $picture]);
    }
}
