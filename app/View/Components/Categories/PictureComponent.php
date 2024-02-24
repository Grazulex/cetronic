<?php

declare(strict_types=1);

namespace App\View\Components\Categories;

use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\View\Component;
use Closure;

final class PictureComponent extends Component
{
    public function __construct(public Category $category, public int $id)
    {
    }

    public function render(): View|Application|Factory|Htmlable|Closure|string|\Illuminate\Contracts\Foundation\Application
    {
        $categoryService = new CategoryService();

        $picture = $categoryService->getDefaultPicture(category: $this->category);

        return view('components.categories.picture-component', ['picture' => $picture, 'id' => $this->id, 'slug' => $this->category->slug]);
    }
}
