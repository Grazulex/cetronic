<?php

declare(strict_types=1);

namespace App\Http\Livewire;

use App\Models\Brand;
use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Livewire\Component;

final class SearchMetas extends Component
{
    public string $slug;

    public string $type;

    public string $catSlug;

    public $selected = [];

    public function render(CategoryService $categoryService): View|Application|Factory|\Illuminate\Contracts\Foundation\Application
    {
        $brand = null;
        $model = Category::where('slug', $this->catSlug)->first();
        if ('brand' === $this->type) {
            $brand = Brand::where('slug', $this->slug)->first();
        }
        $metas = $categoryService->getAllMetasAndValues(category: $model, brand: $brand);

        return view(view: 'livewire.search-metas', data: [
            'metas' => $metas,
        ]);
    }

    public function updatedSelected(): void
    {
        $data = [];

        if (count($this->selected) > 0) {
            foreach ($this->selected as $meta) {
                $parts = explode(separator: '|', string: $meta);
                $data[] = ['meta_id' => $parts[0], 'value' => $parts[1]];
            }
        }

        $this->emit(event: 'searchMetas', data: $data);
    }
}
