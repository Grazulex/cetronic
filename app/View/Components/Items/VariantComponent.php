<?php

declare(strict_types=1);

namespace App\View\Components\Items;

use App\Models\Item;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\Component;

final class VariantComponent extends Component
{
    public Collection $variants;
    public bool $show_picture_variant = false;

    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct(public Item $item)
    {
        $this->variants = Item::where('master_id', $item->id)
            ->orderByRaw('LENGTH(reference) ASC')
            ->orderBy('reference')
            ->enable(auth()->user())
            ->get();
        $this->show_picture_variant = $item->category->show_picture_variant;
    }

    public function render(): View
    {
        $variants = $this->variants;
        $show_picture_variant = $this->show_picture_variant;

        return view('components.items.variant-component', compact('variants', 'show_picture_variant'));
    }
}
