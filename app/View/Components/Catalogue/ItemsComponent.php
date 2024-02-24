<?php

declare(strict_types=1);

namespace App\View\Components\Catalogue;

use App\Models\Item;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class ItemsComponent extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public array $brand)
    {

    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $items = Item::where('brand_id', $this->brand['id'])->get();

        return view('components.catalogue.items-component', compact('items'));
    }
}
