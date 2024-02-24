<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;

final class ListingController extends Controller
{
    public function index(string $catSlug, string $type, string $slug): Application|Factory|View|\Illuminate\Foundation\Application|RedirectResponse
    {
        if ('brand' === $type) {
            $model = Brand::where('slug', $slug)
                ->with('translations', function ($query): void {
                    $query->where('locale', App::currentLocale());
                })
                ->first();
            if ($model) {
                $name = $model->name;
                if (isset($model->translations->first()->description)) {
                    $description = $model->translations->first()->description;
                } else {
                    $description = $model->description;
                }
            } else {
                return redirect()->route('home');
            }
        } else {
            $model = Category::where('slug', $slug)
                ->with('translations', function ($query): void {
                    $query->where('locale', App::currentLocale());
                })
                ->first();
            if ($model) {
                if (isset($model->translations->first()->name)) {
                    $name = $model->translations->first()->name;
                } else {
                    $name = $model->name;
                }
            } else {
                return redirect()->route(route: 'home');
            }

            $description = null;
        }

        return view(view: 'front.pages.listing', data: compact('type', 'slug', 'name', 'description', 'catSlug', 'model'));
    }

    public function search(Request $request): Application|Factory|View|\Illuminate\Foundation\Application|RedirectResponse
    {
        $search = $request->get('search-input');

        $items = Item::where('reference', 'LIKE', '%'.$search.'%')
            ->enable(auth()->user())
            ->with('category')
            ->with('brand')
            ->with('variants')
            ->whereNull('master_id')
            ->orderByRaw(sql: 'LENGTH(reference) ASC')
            ->orderBy('reference')
            ->paginate(21);

        if (count($items) > 1) {
            return view('front.pages.search', compact('search', 'items'));
        } else {
            if (1 === count($items)) {
                return redirect()->route('item', ['item' => $items->first()->slug]);
            } else {
                return redirect()->route('home');
            }
        }
    }

}
