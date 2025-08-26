<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Services\BrandService;
use Closure;
use Illuminate\View\Component;

final class MenuComponent extends Component
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
        $categories = $this->brandService->getAllBrandsAndCategories(auth()->user());
        $categoriesWithPromos = $this->brandService->getCategoriesWithPromos(auth()->user());

        return view('components.menu-component', compact('categories', 'categoriesWithPromos'));
    }
}
