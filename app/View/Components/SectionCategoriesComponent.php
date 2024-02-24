<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Services\CategoryService;
use Illuminate\View\Component;
use Closure;

final class SectionCategoriesComponent extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(private CategoryService $categoryService)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|Closure|string
     */
    public function render()
    {
        $categories = $this->categoryService->getFeaturedCategories(limit: 5);

        return view('components.section-categories-component', compact('categories'));
    }
}
