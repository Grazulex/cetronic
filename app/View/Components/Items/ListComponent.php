<?php

declare(strict_types=1);

namespace App\View\Components\Items;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryMeta;
use App\Models\Item;
use App\Services\CategoryService;
use App\Services\ItemService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
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
                'catalogue' => 'catalog_group',
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
        if ('promo' === $this->type) {
            // Pour les promotions, nous filtrons par catégorie et price_promo
            $mainCategory = Category::where('slug', $this->catSlug)->first();
            
            $query = Item::where('category_id', $mainCategory->id)
                ->where('price_fix', '>', 0)
                ->enable(auth()->user())
                ->with('category')
                ->with('brand')
                ->with('variants')
                ->whereNull('master_id');
            
            $query = $this->addPriceJoinsIfNeeded($query);
            $items = $this->applyOrderingAndPagination($query);
            
            $model = $mainCategory;
            $metas = collect(); // Pas de métadonnées pour les promos
            
            $selected = $this->selected;
            $new = $this->new;
            $order = $this->order;
            $paginate = $this->paginate;

            return view('components.items.list-component', compact('items', 'model', 'metas', 'selected', 'order', 'new', 'paginate'));
            
        } else if ('brand' === $this->type) {
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
                    $query = Item::where($search, $model->id)
                        ->where('category_id', $mainCategory->id)
                        ->where('is_new', true)
                        ->enable(auth()->user());
                    
                    // Appliquer les filtres de métadonnées avec logique AND
                    $query = $this->applyMetadataFilters($query);
                    
                    $query = $query->with('category')
                        ->with('brand')
                        ->with('variants')
                        ->whereNull('master_id');
                        //->orderByRaw('LENGTH('.$this->orderField.') '.$this->orderDirection)
                    $query = $this->addPriceJoinsIfNeeded($query);
                    $items = $this->applyOrderingAndPagination($query);
                } else {
                    $query = Item::where($search, $model->id)
                        ->where('category_id', $mainCategory->id)
                        ->where('is_new', true)
                        ->enable(auth()->user());
                    
                    // Appliquer les filtres de métadonnées avec logique AND
                    $query = $this->applyMetadataFilters($query);
                    
                    $query = $query->with('category')
                        ->with('brand')
                        ->with('variants');
                        //->orderByRaw('LENGTH('.$this->orderField.') '.$this->orderDirection)
                    $query = $this->addPriceJoinsIfNeeded($query);
                    $items = $this->applyOrderingAndPagination($query);
                }
            } else {
                if ( ! $hasVariant) {
                    $query = Item::where($search, $model->id)
                        ->where('category_id', $mainCategory->id)
                        ->enable(auth()->user());
                    
                    // Appliquer les filtres de métadonnées avec logique AND
                    $query = $this->applyMetadataFilters($query);
                    
                    $query = $query->with('category')
                        ->with('brand')
                        ->with('variants')
                        ->whereNull('master_id');
                        //->orderByRaw('LENGTH('.$this->orderField.') '.$this->orderDirection)
                    $query = $this->addPriceJoinsIfNeeded($query);
                    $items = $this->applyOrderingAndPagination($query);
                } else {
                    $query = Item::where($search, $model->id)
                        ->enable(auth()->user())
                        ->where('category_id', $mainCategory->id);
                    
                    // Appliquer les filtres de métadonnées avec logique AND
                    $query = $this->applyMetadataFilters($query);
                    
                    $items = $query->with('category')
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

                    $query = Item::whereIn('id', $showMaster)
                        ->where($search, $model->id)
                        ->where('category_id', $mainCategory->id)
                        ->where('is_published', true)
                        ->with('category')
                        ->with('brand')
                        ->with('variants')
                        ->whereNull('master_id');
                        //->orderByRaw('LENGTH('.$this->orderField.') '.$this->orderDirection)
                    $query = $this->addPriceJoinsIfNeeded($query);
                    $items = $this->applyOrderingAndPagination($query);
                }
            }
        } else {
            $query = Item::where($search, $model->id)
                ->where('category_id', $mainCategory->id)
                ->enable(auth()->user())
                ->with('category')
                ->with('brand')
                ->with('variants')
                ->whereNull('master_id');
                //->orderByRaw('LENGTH('.$this->orderField.') '.$this->orderDirection)
            $query = $this->addPriceJoinsIfNeeded($query);
            $items = $this->applyOrderingAndPagination($query);
        }

        $brand = null;
        if ('promo' === $this->type) {
            // Pour les promos, le modèle est déjà défini
            $categoryService = new CategoryService();
            $metas = collect(); // Pas de métadonnées pour les promos
        } else {
            $model = Category::where('slug', $this->catSlug)->first();
            if ('brand' === $this->type) {
                $brand = Brand::where('slug', $this->slug)->first();
            }
            $categoryService = new CategoryService();
            $metas = $categoryService->getAllMetasAndValues($model, $brand);
        }

        $selected = $this->selected;
        $new = $this->new;
        $order = $this->order;
        $paginate = $this->paginate;

        return view('components.items.list-component', compact('items', 'model', 'metas', 'selected', 'order', 'new', 'paginate'));
    }

    /**
     * Add price-related joins to query when needed for ordering
     */
    private function addPriceJoinsIfNeeded($query)
    {
        // Plus besoin de leftJoin, nous utilisons ItemService pour calculer les prix
        return $query;
    }

    /**
     * Apply ordering and pagination to the query
     */
    private function applyOrderingAndPagination($query)
    {
        if ($this->orderField === 'price') {
            // Pour le tri par prix, nous récupérons tous les items et les trions en PHP
            $allItems = $query->get();
            $user = auth()->user();
            
            // Calculer le prix effectif pour chaque item via ItemService
            $itemsWithPrices = $allItems->map(function ($item) use ($user) {
                $itemService = new ItemService($item);
                $prices = $itemService->getPrice($user);
                $item->calculated_price = $prices['price_end']; // Prix effectif final
                return $item;
            });
            
            // Trier par prix effectif
            if ($this->orderDirection === 'desc') {
                $sortedItems = $itemsWithPrices->sortByDesc(function ($item) {
                    return $item->calculated_price;
                });
            } else {
                $sortedItems = $itemsWithPrices->sortBy(function ($item) {
                    return $item->calculated_price;
                });
            }
            
            // Créer une pagination manuelle
            return $this->createManualPagination($sortedItems);
        } else {
            // Ordre normal pour les autres champs avec pagination standard
            return $query->orderBy($this->orderField, $this->orderDirection)
                         ->paginate($this->paginate)
                         ->withQueryString();
        }
    }

    /**
     * Apply ordering to the query using ItemService for price calculation
     */
    private function applyOrdering($query)
    {
        if ($this->orderField === 'price') {
            // Pour le tri par prix, nous récupérons tous les items et les trions en PHP
            $allItems = $query->get();
            $user = auth()->user();
            
            // Calculer le prix effectif pour chaque item via ItemService
            $itemsWithPrices = $allItems->map(function ($item) use ($user) {
                $itemService = new ItemService($item);
                $prices = $itemService->getPrice($user);
                $item->calculated_price = $prices['price_end']; // Prix effectif final
                return $item;
            });
            
            // Trier par prix effectif
            $sortedItems = $itemsWithPrices->sortBy(function ($item) {
                return $item->calculated_price;
            });
            
            if ($this->orderDirection === 'desc') {
                $sortedItems = $sortedItems->sortByDesc(function ($item) {
                    return $item->calculated_price;
                });
            }
            
            // Créer une pagination manuelle
            return $this->createManualPagination($sortedItems);
        } else {
            // Ordre normal pour les autres champs
            return $query->orderBy($this->orderField, $this->orderDirection);
        }
    }
    
    /**
     * Create manual pagination from a sorted collection
     */
    private function createManualPagination(Collection $items)
    {
        $currentPage = request()->get('page', 1);
        $perPage = $this->paginate;
        
        // Calculer l'offset
        $offset = ($currentPage - 1) * $perPage;
        
        // Prendre seulement les items pour la page actuelle
        $itemsForCurrentPage = $items->slice($offset, $perPage)->values();
        
        // Créer la pagination avec conservation de tous les paramètres
        $paginator = new LengthAwarePaginator(
            $itemsForCurrentPage,
            $items->count(),
            $perPage,
            $currentPage,
            [
                'path' => request()->url(),
                'pageName' => 'page',
            ]
        );
        
        // Conserver tous les paramètres de query string
        return $paginator->withQueryString();
    }

    /**
     * Apply metadata filters using AND logic instead of OR
     * Each selected metadata must be present on the item
     */
    private function applyMetadataFilters($query)
    {
        foreach ($this->selected as $meta => $values) {
            $query->whereHas('metas', function ($q) use ($meta, $values): void {
                $q->where('meta_id', $meta)
                  ->whereIn('value', $values);
            });
        }
        return $query;
    }
}
