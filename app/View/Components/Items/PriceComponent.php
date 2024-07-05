<?php

declare(strict_types=1);

namespace App\View\Components\Items;

use App\Models\Item;
use App\Services\ItemService;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

final class PriceComponent extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public Item $item) {}

    /**
     * Get the view / contents that represent the component.
     *
     * @return View|Closure|string
     */
    public function render(): View|string|Closure
    {
        $itemService = new ItemService($this->item);
        $price = $itemService->getPrice((auth()->user() ? auth()->user() : null));

        return view('components.items.price-component', ['price' => $price]);
    }
}
