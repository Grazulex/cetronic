<?php

declare(strict_types=1);

namespace App\View\Components\Items;

use App\Models\Item;
use App\Models\ItemMeta;
use Illuminate\Support\Facades\App;
use Illuminate\View\Component;
use Closure;

final class MetaComponent extends Component
{
    public $metas = [];

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public Item $item)
    {
        $this->metas = ItemMeta::where('item_id', $item->id)
            ->with('meta.translations', function ($query): void {
                $query->where('locale', App::currentLocale());
            })
            ->get();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|Closure|string
     */
    public function render()
    {
        $metas = $this->metas;

        return view('components.items.meta-component', compact('metas'));
    }
}
