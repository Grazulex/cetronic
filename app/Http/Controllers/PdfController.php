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

        $products = Item::where('is_published', 1)
            ->with('brand', 'metas', 'variants', 'media')
            ->whereHas('metas', function ($query): void {
                $query->where('value', 'Gents')->where('meta_id', 1);
            })
            ->whereHas('metas', function ($query): void {
                $query->where('value', 'Leather')->where('meta_id', 3);
            })
            ->orderBy('brand_id')->orderBy('reference');

        $products  = Item::limit(100);

        $products = $products->limit(24)->get();
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
