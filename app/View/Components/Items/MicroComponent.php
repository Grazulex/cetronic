<?php

declare(strict_types=1);

namespace App\View\Components\Items;

use App\Models\Item;
use App\Services\ItemService;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use Closure;

final class MicroComponent extends Component
{
    public function __construct(public Item $item)
    {
    }

    public function render(): View|string|Closure
    {
        $itemService = new ItemService($this->item);
        $price = $itemService->getPrice((auth()->user() ? auth()->user() : null));

        return view('components.items.micro-component', ['price' => $price]);
    }
}
