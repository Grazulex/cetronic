<?php

declare(strict_types=1);

namespace App\View\Components\Items;

use App\Models\Item;
use App\Services\UserService;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\View\Component;
use Closure;

final class DiscountComponent extends Component
{
    public Collection|array|null $discounts = [];


    public function __construct(public Item $item)
    {
        if (auth()->check()) {
            $userService = new UserService(auth()->user());
            $this->discounts = $userService->getAllBrandAndCategoryDiscounts($this->item->brand, $this->item->category);
        }
    }

    public function render(): View|Application|Factory|Htmlable|Closure|string|\Illuminate\Contracts\Foundation\Application
    {
        $discounts = $this->discounts;

        return view('components.items.discount-component', compact('discounts'));
    }
}
