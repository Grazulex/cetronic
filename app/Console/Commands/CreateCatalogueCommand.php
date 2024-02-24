<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\BrandService;
use Illuminate\Console\Command;
use PDF;

class CreateCatalogueCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-catalogue';

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
        $brandService = new BrandService();
        $brandsOrderByName = $brandService->getAllBrandsAndCategories(auth()->user());

        $pdf = PDF::loadView('pdf.catalogue', compact('brandsOrderByName'));
        $pdf->save(storage_path('app/public/catalogue.pdf'));

    }
}
