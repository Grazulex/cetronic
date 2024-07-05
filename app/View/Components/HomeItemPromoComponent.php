<?php

declare(strict_types=1);

namespace App\View\Components;

use App\Services\CategoryService;
use App\Services\ItemService;
use Closure;
use Illuminate\View\Component;

final class HomeItemPromoComponent extends Component
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
        $itemsPromo = $this->itemService->getPromoItems($this->qty);
        $categoriesPromo = $this->categoryService->getPromoCategories();

        return view('components.home-item-promo-component', compact('itemsPromo', 'categoriesPromo'));
    }
}
