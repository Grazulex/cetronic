<?php

declare(strict_types=1);

namespace App\View\Components\Items;

use App\Models\Item;
use App\Services\ItemService;
use Closure;
use Illuminate\View\Component;

final class PictureComponent extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public Item $item, public string $slug = '') {}

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|Closure|string
     */
    public function render()
    {
        $itemService = new ItemService($this->item);
        $pictures = $itemService->getPictures();
        $isNew = $itemService->isNew();
        $slug = $this->slug;

        return view('components.items.picture-component', ['pictures' => $pictures, 'isNew' => $isNew, 'slug' => $slug]);
    }
}
