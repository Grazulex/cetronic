<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

final class BrandService
{
    public function getAllFeaturedBrands(): Collection
    {
        return Brand::where('is_published', true)
            ->where('is_featured', true)
            ->with('items')
            ->get();
    }

    public function getFeaturedBrandsFromCategory(Category $category): Collection
    {
        return Brand::whereHas('items', function ($query) use ($category): void {
            $query->where('is_published', true)->where('category_id', $category['id']);
        })
            ->with('items')
            ->where('is_published', true)
            ->orderBy('name', 'ASC')->get();
    }

    public function getAllBrandsAndCategories(User $user = null): array
    {
        $data = [];
        $categoryService = new CategoryService();
        $categories = $categoryService->getCategories(user: $user);
        foreach ($categories as $category) {
            if (isset($category['translations'][0]['name'])) {
                $name = str_replace('"', '', $category['translations'][0]['name']);
            } else {
                $name = str_replace('"', '', $category['name']);
            }
            $brands = $this->getBrands(category: $category, user: $user);
            $metasList = [];
            if ($brands->count() > 0) {
                foreach ($brands as $brand) {
                    $metasList[] = ['slug' => $brand['slug'], 'id' => $brand['id'], 'name' => str_replace('"', '', $brand['name'])];
                }
                $data[$category['id']] = ['slug' => $category['slug'], 'id' => $category['id'], 'name' => $name, 'brands' => $metasList];
            }
        }

        return $data;
    }

    public function getDefaultPicture(Brand $brand): string
    {
        $defaultPicture = 'public/brands/default.jpg';
        if ($brand->picture) {
            $defaultPicture = Storage::url(path: $brand->picture);
        }

        return $defaultPicture;
    }

    public function getDefaultPicturePdf(Brand $brand): string
    {
        $defaultPicture = 'public/brands/default.jpg';
        if ($brand->picture) {
            $defaultPicture = $brand->picture;
        }

        return $defaultPicture;
    }

    public function getCatalog(Brand $brand, string $catSlug, User $user): Response
    {
        $mainCategory = Category::where('slug', $catSlug)->first();
        $items = Item::where('brand_id', $brand->id)->where('category_id', $mainCategory->id)->where('is_published', true)->orderBy('reference')->get();
        $pdf = Pdf::loadView('pdf/brand', ['brand' => $brand, 'items' => $items, 'user' => $user])->setPaper('a4', 'landscape');

        return $pdf->stream();
    }

    public function getPictures(Brand $brand, string $catSlug): string
    {
        $mainCategory = Category::where('slug', $catSlug)->first();
        $items = Item::where('brand_id', $brand->id)->where('category_id', $mainCategory->id)->where('is_published', true)->orderBy('reference')->get();
        $zip = new ZipArchive();
        $filename = mb_strtoupper($brand->slug.'.zip');

        $zip->open(
            public_path('/storage/brands/'.$filename),
            ZipArchive::CREATE | ZipArchive::OVERWRITE
        );
        foreach ($items as $item) {
            foreach ($item->getMedia()
                as $file) {
                $zip->addFile(
                    $file->getPath(),
                    $file->file_name
                );
            }
        }
        $zip->close();

        return public_path('/storage/brands/'.$filename);
    }
    private function getBrands($category, User $user = null): Collection
    {
        return  Brand::enabled(auth()->user(), $category)
            ->select(['name', 'id', 'slug'])
            ->whereHas('items', function ($query) use ($category): void {
                $query->where('is_published', true)->where('category_id', $category['id']);
            })
            ->orderBy('name', 'ASC')->get();
    }
}
