<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Models\Category;
use App\Services\BrandService;
use Closure;
use Illuminate\View\Component;

final class MenuFeaturedComponent extends Component
{
    /**
     * __construct
     *
     * @param  array<string>  $arrayCategory
     * @param  Category  $category
     * @param  BrandService  $brandService
     * @return void
     */
    public function __construct(public array $arrayCategory, private Category $category, private BrandService $brandService)
    {
        $this->category = Category::find($arrayCategory['id']);
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|Closure|string
     */
    public function render()
    {
        $brandsFeatured = $this->brandService->getFeaturedBrandsFromCategory($this->category);

        return view('components.menu-featured-component', compact('brandsFeatured'));
    }
}
