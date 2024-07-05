<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Services\CategoryService;
use App\Services\ItemService;
use Closure;
use Illuminate\View\Component;

final class HomeItemNewComponent extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(private CategoryService $categoryService, private ItemService $itemService, public int $qty = 8) {}

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|Closure|string
     */
    public function render()
    {
        $itemsNew = $this->itemService->getNewItems($this->qty);
        $categoriesNew = $this->categoryService->getNewCategories();

        return view('components.home-item-new-component', compact('itemsNew', 'categoriesNew'));
    }
}
