<?php

namespace App\Jobs;

use App\Enum\PdfGeneratorStatusEnum;
use App\Models\PdfCatalog;
use App\Models\Item;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class PdfGenerate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    const STORAGE_PDF_DIR = 'app/public/pdf/';
    const FILE_NAME_PREFIX = 'catalog_';
    const FILE_NAME_POSTFIX = '.pdf';

    /**
     * Create a new job instance.
     */
    public function __construct(public PdfCatalog $pdfCatalog)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $pdfCatalog = $this->pdfCatalog;
        $pdfConditions = $pdfCatalog->conditions;
        $products = Item::with('brand', 'metas', 'variants', 'media');
        $brandConditionNames = $pdfCatalog->getConditionBrandsAttribute();
        $typeConditionNames = $pdfCatalog->getConditionTypesAttribute();
        $genderConditionNames = $pdfCatalog->getConditionGendersAttribute();
        if (!empty($pdfConditions[PdfCatalog::CONDITION_BRAND])) {
            $products->whereHas('brand', function ($query) use ($pdfConditions) {
                $query->whereIn('name', $pdfConditions[PdfCatalog::CONDITION_BRAND]);
            });
        }
        if (!empty($pdfConditions[PdfCatalog::CONDITION_TYPE])) {
            $products->whereHas('category', function ($query) use ($pdfConditions) {
                $query->whereIn('name', $pdfConditions[PdfCatalog::CONDITION_TYPE]);
            });
        }
        if (!empty($pdfConditions[PdfCatalog::CONDITION_GENDER])) {
            $products->whereIn('gender', $pdfConditions[PdfCatalog::CONDITION_GENDER]);
        }
        $products = $products->get();

        $pdf = Pdf::loadView(
            'pdf.catalog',
            compact('brandConditionNames', 'typeConditionNames', 'genderConditionNames', 'products')
        )->setPaper('a4', 'landscape');
        $concatConditions = implode('_', $genderConditionNames) . ' '
            . implode('_', $brandConditionNames) . ' '
            . implode('_', $typeConditionNames);
        $url = self::STORAGE_PDF_DIR
            . self::FILE_NAME_PREFIX
            . str_replace(' ', '_', trim($concatConditions))
            . self::FILE_NAME_POSTFIX;

        $pdf->save(storage_path($url));
        $pdfCatalog->url = $url;
        $pdfCatalog->status = PdfGeneratorStatusEnum::GENERATED;
        $pdfCatalog->save();
    }
}
