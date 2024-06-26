<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Enum\PdfGeneratorStatusEnum;
use App\Models\Item;
use App\Models\PdfCatalog;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class PdfGenerate implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

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
     * Create a new job instance.
     */
    public function __construct(public PdfCatalog $pdfCatalog) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $pdfCatalog = $this->pdfCatalog;
        $pdfConditions = $pdfCatalog->conditions;
        $products = Item::with('brand', 'metas', 'variants', 'media');
        $brandConditionNames = $pdfCatalog->getConditionBrandsAttribute();
        $categoryConditionNames = $pdfCatalog->getConditionCategoriesAttribute();
        $typeConditionNames = $pdfCatalog->getConditionTypesAttribute();
        $genderConditionNames = $pdfCatalog->getConditionGendersAttribute();
        if ( ! empty($pdfConditions[PdfCatalog::CONDITION_BRAND])) {
            $products->whereIn('brand_id', $pdfConditions[PdfCatalog::CONDITION_BRAND]);
            unset($pdfConditions[PdfCatalog::CONDITION_BRAND]);
        }
        if ( ! empty($pdfConditions[PdfCatalog::CONDITION_CATEGORY])) {
            $products->whereIn('category_id', $pdfConditions[PdfCatalog::CONDITION_CATEGORY]);
            unset($pdfConditions[PdfCatalog::CONDITION_CATEGORY]);
        }
        $this->applyMetaConditions($products, $pdfConditions);

        $products = $products->get();

        $pdf = Pdf::loadView(
            'pdf.catalog',
            compact(
                'brandConditionNames',
                'categoryConditionNames',
                'typeConditionNames',
                'genderConditionNames',
                'products',
            ),
        )->setPaper('a4', 'landscape');

        $concatConditions = implode('_', $genderConditionNames) . ' '
            . implode('_', $brandConditionNames) . ' '
            . implode('_', $categoryConditionNames) . ' '
            . implode('_', $typeConditionNames);

        $fileName = self::FILE_NAME_PREFIX
            . str_replace(' ', '_', trim($concatConditions))
            . self::FILE_NAME_POSTFIX;

        if ( ! Storage::directories('public/pdf')) {
            Storage::makeDirectory('public/pdf');
        }

        $pdf->save(storage_path(self::STORAGE_PDF_DIR . $fileName));

        $pdfCatalog->url = 'pdf/' . $fileName;
        $pdfCatalog->status = PdfGeneratorStatusEnum::GENERATED;
        $pdfCatalog->save();
    }

    public function applyMetaConditions($products, $pdfConditions)
    {
        foreach ($pdfConditions as $conditionName => $condition) {
            if ( ! empty($condition)) {
                $products->whereHas('metas', function ($query) use ($condition, $conditionName): void {
                    $query->whereIn('value', $condition);
                    $query->whereHas('meta', function ($q) use ($conditionName): void {
                        $PdfCatalogClass = PdfCatalog::class;
                        $q->where('name', constant($PdfCatalogClass . '::META_' . mb_strtoupper($conditionName)));
                    });
                });
            }
        }
        return $products;
    }
}
