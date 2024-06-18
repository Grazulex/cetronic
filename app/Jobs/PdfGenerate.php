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
            $products->whereIn('brand_id', $pdfConditions[PdfCatalog::CONDITION_BRAND]);
            unset($pdfConditions[PdfCatalog::CONDITION_BRAND]);
        }
        $this->applyMetaConditions($products, $pdfConditions);

        $products = $products->get();

        $pdf = Pdf::loadView(
            'pdf.catalog',
            compact(
                'brandConditionNames',
                'typeConditionNames',
                'genderConditionNames',
                'products'
            )
        )->setPaper('a4', 'landscape');
        $concatConditions = implode('_', $genderConditionNames) . ' '
            . implode('_', $brandConditionNames) . ' '
            . implode('_', $typeConditionNames);
        $fileName = self::FILE_NAME_PREFIX
            . str_replace(' ', '_', trim($concatConditions))
            . self::FILE_NAME_POSTFIX;

        $pdf->save(storage_path(self::STORAGE_PDF_DIR . $fileName));
        $pdfCatalog->url = 'pdf/' . $fileName;
        $pdfCatalog->status = PdfGeneratorStatusEnum::GENERATED;
        $pdfCatalog->save();
    }

    public function applyMetaConditions($products, $pdfConditions)
    {
        foreach ($pdfConditions as $conditionName => $condition) {
            if (!empty($condition)) {
                $products->whereHas('metas', function ($query) use ($condition, $conditionName) {
                    $query->whereIn('value', $condition);
                    $query->whereHas('meta', function ($q) use ($conditionName) {
                        $PdfCatalogClass = PdfCatalog::class;
                        $q->where('name', constant($PdfCatalogClass . '::META_' . strtoupper($conditionName)));
                    });
                });
            }
        }
        return $products;
    }
}
