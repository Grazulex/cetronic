<?php

declare(strict_types=1);

namespace App\View\Components\Items;

use App\Models\Item;
use App\Services\ItemService;
use Closure;
use Illuminate\View\Component;

final class Picture2Component extends Component
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
     * @return \Illuminate\Contracts\View\View|Closure|string
     */
    public function render()
    {
        $itemService = new ItemService($this->item);
        $picture = $itemService->getDefaultPicture();
        $isNew = $itemService->isNew();

        return view('components.items.picture2-component', ['picture' => $picture, 'isNew' => $isNew]);
    }
}
