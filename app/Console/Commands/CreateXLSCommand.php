<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Exports\CategoriesExport;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Console\Command;
use Maatwebsite\Excel\Facades\Excel;

final class CreateXLSCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-xls-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $category = Category::find(1);
        $brand = Brand::find(5);

        $name = 'cat'.$category->id.'_brand'.$brand->id.'.xlsx';

        $this->info('Creating '.$name);
        Excel::store(new CategoriesExport($category, $brand), $name, 'public');
        $this->info('Done');
    }
}
