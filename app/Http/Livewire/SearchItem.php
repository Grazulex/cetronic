<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use App\Models\Item;
use Livewire\Component;

final class SearchItem extends Component
{
    public $term = '';

    public function render()
    {
        if ($this->term && mb_strlen($this->term) > 2) {
            $items = Item::where('reference', 'LIKE', "%{$this->term}%")
                ->enable(auth()->user())
                ->whereHas('brand', function ($query): void {
                    $query->enabled(auth()->user());
                })
                ->whereHas('category', function ($query): void {
                    $query->enabled();
                })
                ->with('category')
                ->with('brand')
                ->with('variants')
                ->orderByRaw(sql: 'LENGTH(reference) ASC')
                ->orderBy(column: 'reference')
                ->paginate(perPage: 10);
        } else {
            $items = collect();
        }

        return view(view: 'livewire.search-item', data: [
            'items' => $items,
        ]);
    }
}
