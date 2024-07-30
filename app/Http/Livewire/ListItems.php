<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\App;
use Livewire\Component;

final class ListItems extends Component
{
    public string $slug;

    public string $type;

    public string $catSlug;

    protected $selected = [];

    protected $listeners = [
        'searchMetas' => 'setSelected',
    ];

    public function setSelected($selected): void
    {
        if (count($selected) > 0) {
            $this->selected = $selected;
        } else {
            $this->selected = [];
        }
    }

    public function render(): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        if ('brand' === $this->type) {
            $model = Brand::where('slug', $this->slug)->first();
            $search = 'brand_id';
        } else {
            $model = Category::where('slug', $this->slug)
                ->with('translations', function ($query): void {
                    $query->where('locale', App::currentLocale());
                })
                ->first();
            $search = 'category_id';
        }

        $mainCategory = Category::where('slug', $this->catSlug)->first();

        if (count($this->selected) > 0) {
            $items = Item::where($search, $model->id)
                ->where('category_id', $mainCategory->id)
                ->enable(auth()->user())
                ->WhereHas(relation: 'metas', callback: function ($query): void {
                    foreach ($this->selected as $meta) {
                        $value = $meta['value'];
                        //$query->orWhere(function ($query) use ($meta, $value) {
                        $query->where('meta_id', $meta['meta_id'])
                            ->where('value', $value);
                        //});
                    }
                })
                ->with('category')
                ->with('brand')
                ->with('variants')
                ->whereNull(columns: 'master_id')
                //->orderByRaw(sql: 'LENGTH(reference) ASC')
                ->orderBy('catalog_group')
                ->orderBy(column: 'reference')
                ->paginate(perPage: 21);
        } else {
            $items = Item::where($search, $model->id)
                ->where('category_id', $mainCategory->id)
                ->enable(auth()->user())
                ->with('category')
                ->with('brand')
                ->with('variants')
                ->whereNull(columns: 'master_id')
                ->orderBy('catalog_group')
                ->orderBy(column: 'reference')
                ->paginate(perPage: 21);
        }

        return view(view: 'livewire.list-items', data: compact('items', 'model'));
    }
}
