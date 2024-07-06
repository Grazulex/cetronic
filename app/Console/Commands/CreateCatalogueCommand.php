<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Item;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CreateCatalogueCommand extends Command
{
    /**
     * Execute the console command.
     */
    public const STORAGE_PDF_DIR = 'app/public/pdf/';
    public const FILE_NAME_PREFIX = 'catalog_';
    public const FILE_NAME_POSTFIX = '.pdf';
    /**
     * The number of seconds the job can run before timing out.
     *
     * @var int
     */
    public $timeout = 9000;
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
     * Execute the job.
     */
    public function handle(): void
    {
        //Gents lists
        $products = Item::where('is_published', 1)
            ->with('brand', 'metas', 'variants', 'media')
            ->whereHas('metas', function ($query): void {
                $query->where('value', 'Gents')->where('meta_id', 1);
            })
            ->whereHas('metas', function ($query): void {
                $query->where('value', 'Leather')->where('meta_id', 3);
            })
            ->orderBy('brand_id')->orderBy('reference');

        $productsToPrint = $products->get();

        $products = Item::where('is_published', 1)
            ->with('brand', 'metas', 'variants', 'media')
            ->whereHas('metas', function ($query): void {
                $query->where('value', 'Gents')->where('meta_id', 1);
            })
            ->whereHas('metas', function ($query): void {
                $query->where('value', 'Stainless Steel')->where('meta_id', 3);
            })
            ->orderBy('brand_id')->orderBy('reference');

        $productsToPrint2 = $products->get();

        $products = Item::where('is_published', 1)
            ->with('brand', 'metas', 'variants', 'media')
            ->whereHas('metas', function ($query): void {
                $query->where('value', 'Gents')->where('meta_id', 1);
            })
            ->whereHas('metas', function ($query): void {
                $query->where('value', 'Silicone')->where('meta_id', 3);
            })
            ->orderBy('brand_id')->orderBy('reference');

        $productsToPrint3 = $products->get();

        $products = Item::where('is_published', 1)
            ->with('brand', 'metas', 'variants', 'media')
            ->whereHas('metas', function ($query): void {
                $query->where('value', 'Gents')->where('meta_id', 1);
            })
            ->whereHas('metas', function ($query): void {
                $query->where('value', '!=', 'Leather')
                    ->where('value', '!=', 'Silicone')
                    ->where('value', '!=', 'Stainless Steel')
                    ->where('meta_id', 3);
            })
            ->orderBy('brand_id')->orderBy('reference');

        $productsToPrint4 = $products->get();

        //Ladies lists

        //combine products
        $products = $productsToPrint->merge($productsToPrint2)->merge($productsToPrint3)->merge($productsToPrint4);

        $pdf = Pdf::loadView(
            'pdf.catalog',
            compact(
                'products',
            ),
        )->setPaper('a4', 'landscape');

        $fileName = self::FILE_NAME_PREFIX
            . 'Hommes'
            . self::FILE_NAME_POSTFIX;

        if ( ! Storage::directories('public/pdf')) {
            Storage::makeDirectory('public/pdf');
        }

        $pdf->save(storage_path(self::STORAGE_PDF_DIR . $fileName));
    }
}
