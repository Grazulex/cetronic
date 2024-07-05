<?php

declare(strict_types=1);

namespace App\View\Components\Items;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryMeta;
use App\Models\Item;
use App\Services\CategoryService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\View\Component;

final class ListComponent extends Component
{
    private $selected = [];

    private $new = '';

    private $order = 'reference_asc';

    private int $paginate = 21;

    private $orderField;

    private $orderDirection;

    /**
     * Create a new component instance.
     * @param string $slug
     * @param string $type
     * @param string $catSlug
     * @param Request $request
     * @return void
     *
     */
    public function __construct(public string $slug, public string $type, public string $catSlug, Request $request)
    {
        if ($request->has('metas')) {
            $this->selected = $request->get('metas');
        }

        if ($request->has('new')) {
            $this->new = $request->get('new');
        } else {
            $this->new = null;
        }

        if ($request->has('order')) {
            $this->order = $request->get('order');

            $this->orderField = match ($request->get('order')) {
                'price_asc', 'price_desc' => 'price',
                default => 'reference',
            };

            $this->orderDirection = match ($request->get('order')) {
                'reference_desc', 'price_desc' => 'desc',
                default => 'asc',
            };
        } else {
            $this->orderField = 'reference';
            $this->orderDirection = 'asc';
        }

        if ($request->has('paginate')) {
            $this->paginate = (int) $request->get('paginate');
        }
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|Closure|string
     */
    public function render()
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

        $hasVariant = false;
        foreach ($this->selected as $meta => $values) {
            if (CategoryMeta::find($meta)->is_variant) {
                $hasVariant = true;
            }
        }

        if (count($this->selected) > 0 || '' !== $this->new) {
            if ('1' === $this->new) {
                if ( ! $hasVariant) {
                    $items = Item::where($search, $model->id)
                        ->where('category_id', $mainCategory->id)
                        ->where('is_new', true)
                        ->enable(auth()->user())
                        ->whereHas('metas', function ($query): void {
                            foreach ($this->selected as $meta => $values) {
                                $query->whereIn('item_id', function ($query) use ($meta, $values): void {
                                    $query->select('item_id')
                                        ->from('item_metas')
                                        ->where('meta_id', $meta)
                                        ->whereIn('value', $values);
                                });
                            }
                        })
                        ->with('category')
                        ->with('brand')
                        ->with('variants')
                        ->whereNull('master_id')
                        //->orderByRaw('LENGTH('.$this->orderField.') '.$this->orderDirection)
                        ->orderBy($this->orderField, $this->orderDirection)
                        ->paginate($this->paginate)
                        ->withQueryString();
                } else {
                    $items = Item::where($search, $model->id)
                        ->where('category_id', $mainCategory->id)
                        ->where('is_new', true)
                        ->enable(auth()->user())
                        ->whereHas('metas', function ($query): void {
                            foreach ($this->selected as $meta => $values) {
                                $query->whereIn('item_id', function ($query) use ($meta, $values): void {
                                    $query->select('item_id')
                                        ->from('item_metas')
                                        ->where('meta_id', $meta)
                                        ->whereIn('value', $values);
                                });
                            }
                        })
                        ->with('category')
                        ->with('brand')
                        ->with('variants')
                        //->orderByRaw('LENGTH('.$this->orderField.') '.$this->orderDirection)
                        ->orderBy($this->orderField, $this->orderDirection)
                        ->paginate($this->paginate)
                        ->withQueryString();
                }
            } else {
                if ( ! $hasVariant) {
                    $items = Item::where($search, $model->id)
                        ->where('category_id', $mainCategory->id)
                        ->enable(auth()->user())
                        ->whereHas('metas', function ($query): void {
                            foreach ($this->selected as $meta => $values) {
                                $query->whereIn('item_id', function ($query) use ($meta, $values): void {
                                    $query->select('item_id')
                                        ->from('item_metas')
                                        ->where('meta_id', $meta)
                                        ->whereIn('value', $values);
                                });
                            }
                        })
                        ->with('category')
                        ->with('brand')
                        ->with('variants')
                        ->whereNull('master_id')
                        //->orderByRaw('LENGTH('.$this->orderField.') '.$this->orderDirection)
                        ->orderBy($this->orderField, $this->orderDirection)
                        ->paginate($this->paginate)
                        ->withQueryString();
                } else {
                    $items = Item::where($search, $model->id)
                        ->enable(auth()->user())
                        ->where('category_id', $mainCategory->id)
                        ->whereHas('metas', function ($query): void {
                            foreach ($this->selected as $meta => $values) {
                                $query->whereIn('item_id', function ($query) use ($meta, $values): void {
                                    $query->select('item_id')
                                        ->from('item_metas')
                                        ->where('meta_id', $meta)
                                        ->whereIn('value', $values);
                                });
                            }
                        })
                        ->with('category')
                        ->with('brand')
                        ->with('variants')->get();

                    $showMaster = [];
                    foreach ($items as $item) {
                        if (null !== $item->master_id) {
                            $showMaster[] = $item->master_id;
                        } else {
                            $showMaster[] = $item->id;
                        }
                    }

                    $items = Item::whereIn('id', $showMaster)
                        ->where($search, $model->id)
                        ->where('category_id', $mainCategory->id)
                        ->where('is_published', true)
                        ->with('category')
                        ->with('brand')
                        ->with('variants')
                        ->whereNull('master_id')
                        //->orderByRaw('LENGTH('.$this->orderField.') '.$this->orderDirection)
                        ->orderBy($this->orderField, $this->orderDirection)
                        ->paginate($this->paginate)
                        ->withQueryString();
                }
            }
        } else {
            $items = Item::where($search, $model->id)
                ->where('category_id', $mainCategory->id)
                ->enable(auth()->user())
                ->with('category')
                ->with('brand')
                ->with('variants')
                ->whereNull('master_id')
                //->orderByRaw('LENGTH('.$this->orderField.') '.$this->orderDirection)
                ->orderBy($this->orderField, $this->orderDirection)
                ->paginate($this->paginate)
                ->withQueryString();
        }

        $brand = null;
        $model = Category::where('slug', $this->catSlug)->first();
        if ('brand' === $this->type) {
            $brand = Brand::where('slug', $this->slug)->first();
        }
        $categoryService = new CategoryService();
        $metas = $categoryService->getAllMetasAndValues($model, $brand);

        $selected = $this->selected;
        $new = $this->new;
        $order = $this->order;
        $paginate = $this->paginate;

        return view('components.items.list-component', compact('items', 'model', 'metas', 'selected', 'order', 'new', 'paginate'));
    }
}
