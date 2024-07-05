<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Services\BrandService;
use Closure;
use Illuminate\View\Component;

final class HomeBrandFeaturedComponent extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(private BrandService $brandService) {}

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|Closure|string
     */
    public function render()
    {
        $brandsFeatured = $this->brandService->getAllFeaturedBrands();

        return view('components.home-brand-featured-component', compact('brandsFeatured'));
    }
}
