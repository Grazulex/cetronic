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
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Facades\Image;
use Throwable;

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

    /**
     * Filter groups of a category with values and counts limited to the given (master) items and their variants,
     * e.g. the items currently on promotion. Each master item is counted once per value.
     *
     * @param  array<int, int>  $itemIds
     * @return array<int, array{id: int, name: string, is_color: mixed, values: array<int, array{value: string, count: int}>}>
     */
    public function getMetasAndValuesForItems(Category $category, array $itemIds): array
    {
        $data = [];

        foreach ($this->getMetas(category: $category) as $meta) {
            $name = str_replace('"', '', $meta['translations'][0]['name'] ?? $meta['name']);
            $values = ItemMeta::query()
                ->join('items', 'items.id', '=', 'item_metas.item_id')
                ->where('item_metas.meta_id', $meta['id'])
                ->where(fn($query) => $query->whereIn('items.id', $itemIds)->orWhereIn('items.master_id', $itemIds))
                ->groupBy('item_metas.value')
                ->orderBy('item_metas.value')
                ->selectRaw('item_metas.value as value, COUNT(DISTINCT COALESCE(items.master_id, items.id)) as total')
                ->get();

            if ($values->isNotEmpty()) {
                $data[$meta['id']] = [
                    'id' => $meta['id'],
                    'name' => $name,
                    'is_color' => $meta['is_color'],
                    'values' => $values->map(fn(ItemMeta $value): array => ['value' => str_replace('"', '', (string) $value->getAttribute('value')), 'count' => (int) $value->getAttribute('total')])->all(),
                ];
            }
        }

        return $data;
    }

    public function getDefaultPicture(Category $category): string
    {
        $defaultPicture = 'public/categories/default.jpg';

        if ($category->picture) {
            $webpPicture = self::webpPath($category->picture);
            $defaultPicture = Storage::url(path: Storage::disk('public')->exists($webpPicture) ? $webpPicture : $category->picture);
        }

        return $defaultPicture;
    }

    /**
     * Crée une version WebP (même dimensions) de l'image de la catégorie, servie à la place de l'original.
     */
    public function generateWebpPicture(Category $category): bool
    {
        $disk = Storage::disk('public');

        if ( ! $category->picture || ! $disk->exists($category->picture)) {
            return false;
        }

        $webpPicture = self::webpPath($category->picture);
        if ($webpPicture === $category->picture) {
            return false;
        }

        try {
            Image::make($disk->path($category->picture))
                ->encode('webp', 85)
                ->save($disk->path($webpPicture));
        } catch (Throwable $exception) {
            Log::warning('Category webp picture generation failed', ['category' => $category->id, 'error' => $exception->getMessage()]);

            return false;
        }

        return true;
    }

    public static function webpPath(string $path): string
    {
        return preg_replace('/\.[^.\/]+$/', '', $path) . '.webp';
    }
}
