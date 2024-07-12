<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Item;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CreateCatalogueHommeCommand extends Command
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
    protected $signature = 'app:create-catalogue-homme';
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

        $products = Item::where('is_published', 1)
            ->with('brand', 'metas', 'variants', 'media')
            ->orderBy('catalog_group')->orderBy('reference')->get();

        $pdf = Pdf::loadView(
            'pdf.catalog',
            compact(
                'products',
            ),
        )->setPaper('a4', 'landscape');

        $fileName = self::FILE_NAME_PREFIX
            . 'Full'
            . self::FILE_NAME_POSTFIX;

        if ( ! Storage::directories('public/pdf')) {
            Storage::makeDirectory('public/pdf');
        }

        $pdf->save(storage_path(self::STORAGE_PDF_DIR . $fileName));
    }
}
