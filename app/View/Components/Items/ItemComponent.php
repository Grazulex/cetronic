<?php

declare(strict_types=1);

namespace App\View\Components\Items;

use App\Models\Item;
use Illuminate\View\Component;
use Closure;

final class ItemComponent extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public Item $item)
    {
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|Closure|string
     */
    public function render()
    {
        $item = $this->item;

        return view('components.items.item-component', compact('item'));
    }
}
