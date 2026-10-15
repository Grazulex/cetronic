<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Category;
use App\Services\CategoryService;
use Illuminate\Console\Command;

final class GenerateCategoryWebpCommand extends Command
{
    protected $signature = 'categories:generate-webp';

    protected $description = 'Generate the webp version of every category picture';

    public function handle(CategoryService $categoryService): int
    {
        Category::whereNotNull('picture')->each(function (Category $category) use ($categoryService): void {
            $done = $categoryService->generateWebpPicture($category);
            $this->line(($done ? 'OK   ' : 'SKIP ') . $category->id . ' ' . $category->picture);
        });

        return self::SUCCESS;
    }
}
