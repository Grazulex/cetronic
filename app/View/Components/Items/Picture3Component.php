<?php

declare(strict_types=1);

namespace App\View\Components\Items;

use App\Models\Item;
use App\Services\ItemService;
use Closure;
use Illuminate\View\Component;

final class Picture3Component extends Component
{
    /**
     * Create a new component instance. (bootstrap carousel  with picture thumbnail)
     *
     * @param Item Displayed item
     * @param itemCarouselId Displayed html id for the product carousel.
     * @param showThumb Show or hide thumbnail for the current carousel.
     *
     * @return void
     */
    public function __construct(
        public Item $item,
        public string $slug = '',
        public string $itemCarouselId = 'myCarousel',
        public bool $showThumb = true,
    ) {}

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
        $isBestSeller = $itemService->isBestSeller();
        $slug = $this->slug;

        return view('components.items.picture3-component', ['pictures' => $pictures, 'isNew' => $isNew, 'isBestSeller' => $isBestSeller, 'slug' => $slug]);
    }
}
