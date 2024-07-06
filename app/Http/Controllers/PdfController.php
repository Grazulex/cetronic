<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Item;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

final class PdfController extends Controller
{
    public function index()
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
                $query->where('value', 'Silicone')->where('meta_id', 3);
            })
            ->orderBy('brand_id')->orderBy('reference');

        $productsToPrint2 = $products->get();

        $products = Item::where('is_published', 1)
            ->with('brand', 'metas', 'variants', 'media')
            ->whereHas('metas', function ($query): void {
                $query->where('value', 'Gents')->where('meta_id', 1);
            })
            ->whereHas('metas', function ($query): void {
                $query->where('value', '!=', 'Leather')
                    ->where('value', '!=', 'Silicone')
                    ->where('meta_id', 3);
            })
            ->orderBy('brand_id')->orderBy('reference');

        $productsToPrint3 = $products->get();

        //Ladies lists

        //combine products
        $products = $productsToPrint->merge($productsToPrint2)->merge($productsToPrint3);

        //create pdf
        $pdf = Pdf::loadView(
            'pdf.catalog',
            compact(
                'products',
            ),
        )->setPaper('a4', 'landscape');

        if ( ! Storage::directories('public/pdf')) {
            Storage::makeDirectory('public/pdf');
        }

        return $pdf->stream('pdf.catalog');
    }
}
