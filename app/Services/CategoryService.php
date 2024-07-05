<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\CategoryMeta;
use App\Models\ItemMeta;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

final class CategoryService
{
    public function getCategories(?User $user = null): Collection
    {
        return Category::enabled(auth()->user())
            ->select(['id', 'name', 'slug'])
            ->with('translations', function ($query): void {
                $query->where('locale', App::currentLocale());
            })
            ->orderBy('order')
            ->get();
    }

    public function getFeaturedCategories(int $limit = 99): Collection
    {
        return Category::select(['name', 'id', 'picture', 'slug'])
            ->with('translations', function ($query): void {
                $query->where('locale', App::currentLocale());
            })
            ->where('is_featured', true)
            ->where('is_published', true)
            ->limit($limit)
            ->orderBy('name', 'ASC')
            ->get();
    }

    public function getPromoCategories(): Collection
    {
        return Category::select(['name', 'id'])
            ->with('translations', function ($query): void {
                $query->where('locale', App::currentLocale());
            })
            ->whereHas('items', function ($query): void {
                $query->where('price_promo', '>', 0);
            })
            ->enabled(auth()->user())
            ->orderBy('name', 'ASC')
            ->get();
    }

    public function getNewCategories(): Collection
    {
        return Category::select(['name', 'id'])
            ->with('translations', function ($query): void {
                $query->where('locale', App::currentLocale());
            })
            ->whereHas('items', function ($query): void {
                $query->where('is_new', true);
            })
            ->enabled(auth()->user())
            ->orderBy('name', 'ASC')
            ->get();
    }

    public function getMetas(Category $category): Collection
    {
        return CategoryMeta::select(['name', 'id', 'is_color'])
            ->with('translations', function ($query): void {
                $query->where('locale', App::currentLocale());
            })
            ->where('category_id', $category->id)
            ->where('is_meta', true)
            ->groupBy('name', 'id', 'is_color')
            ->orderBy('name', 'ASC')
            ->get();
    }

    public function getMetaValues(int $meta_id, ?Brand $brand): null|Collection
    {
        if ($brand) {
            return ItemMeta::select('value')
                ->selectRaw('count(*) as total')
                ->where('meta_id', $meta_id)
                ->with('item')
                ->whereHas('item', function ($query) use ($brand): void {
                    $query->where('is_published', true)
                        ->where('brand_id', $brand->id)
                        ->whereNotIn('id', function ($query): void {
                            $query->select('item_id')
                                ->from('user_disables')
                                ->where('user_id', auth()->id())
                                ->where('is_enable', true);
                        });
                })
                ->groupBy('value')
                ->orderBy('value')
                ->get();
        } else {
            return ItemMeta::select('value')
                ->selectRaw('count(*) as total')
                ->where('meta_id', $meta_id)
                ->with('item')
                ->whereHas('item', function ($query): void {
                    $query->where('is_published', true)
                        ->whereNotIn('id', function ($query): void {
                            $query->select('item_id')
                                ->from('user_disables')
                                ->where('user_id', auth()->id())
                                ->where('is_enable', true);
                        });
                })
                ->with('item')
                ->groupBy('value')
                ->orderBy('value')
                ->get();
        }
    }

    public function getAllMetasAndValues(Category $category, ?Brand $brand): array
    {
        $data = [];
        $metas = $this->getMetas(category: $category);
        foreach ($metas as $meta) {
            if (isset($meta['translations'][0]['name'])) {
                $name = str_replace('"', '', $meta['translations'][0]['name']);
            } else {
                $name = str_replace('"', '', $meta['name']);
            }
            $values = $this->getMetaValues($meta['id'], $brand);
            $valuesList = [];
            if (count($values) > 0) {
                foreach ($values as $value) {
                    $valuesList[] = ['value' => str_replace('"', '', $value['value']), 'count' => $value['total']];
                }
                $data[$meta['id']] = ['id' => $meta['id'], 'name' => $name, 'is_color' => $meta['is_color'], 'values' => $valuesList];
            }
        }

        return $data;
    }

    public function getDefaultPicture(Category $category): string
    {
        $defaultPicture = 'public/categories/default.jpg';

        if ($category->picture) {
            $defaultPicture = Storage::url(path: $category->picture);
        }

        return $defaultPicture;
    }
}
