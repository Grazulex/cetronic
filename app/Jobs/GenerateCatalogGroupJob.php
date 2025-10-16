<?php

namespace App\Jobs;

use App\Models\Item;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class GenerateCatalogGroupJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 1200; // 20 minutes max par groupe (pour gros catalogues comme 320)

    public $tries = 2; // 2 tentatives en cas d'échec

    /**
     * @param string $catalogGroup Le numéro du groupe (ex: "010", "015", etc.)
     * @param string $genderFilter Le filtre de genre ("Gents", "Ladies", "Kids", null pour unisex)
     * @param string $catalogName Le nom du catalogue (ex: "Hommes", "Femmes", etc.)
     */
    public function __construct(
        public string $catalogGroup,
        public ?string $genderFilter,
        public string $catalogName
    ) {
        // Définir la queue via la méthode du trait Queueable
        $this->onQueue('catalog');
    }

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

        // Augmenter les limites PHP pour les gros catalogues
        ini_set('max_execution_time', '1200'); // 20 minutes
        ini_set('memory_limit', '1024M'); // 1GB de mémoire

        $startTime = microtime(true);

        Log::info("Génération du groupe {$this->catalogGroup} pour {$this->catalogName}");

        // Construire la requête
        $query = Item::where('is_published', 1)
            ->where('catalog_group', $this->catalogGroup)
            ->select(['id', 'reference', 'catalog_group', 'reseller_price']);

        // Appliquer le filtre de genre si spécifié
        // Si null ET catalogName != 'Full' : produits sans genre (Unisex)
        // Si null ET catalogName == 'Full' : TOUS les produits
        if ($this->genderFilter !== null) {
            $query->whereHas('metas', function (Builder $q): void {
                $q->where('value', $this->genderFilter)->where('meta_id', 1);
            });
        } elseif ($this->catalogName !== 'Full') {
            // Pour catalogues spécifiques Unisex : exclure Gents, Ladies, Kids
            $query->whereDoesntHave('metas', function (Builder $q): void {
                $q->where('meta_id', 1)
                    ->whereIn('value', ['Gents', 'Ladies', 'Kids']);
            });
        }
        // Si catalogName == 'Full' et genderFilter == null : pas de filtre = TOUS les produits

        // Eager load relations - optimisé pour limiter la mémoire
        $query->with([
            'brand:id,name',
            'metas' => fn($q) => $q->select(['id', 'item_id', 'meta_id', 'value'])->with('meta:id,name'),
            'media' => fn($q) => $q->select(['id', 'model_id', 'model_type', 'file_name', 'disk', 'collection_name'])
        ])->orderBy('reference');

        // Utiliser lazy() au lieu de get() pour économiser la mémoire si gros volume
        $productsCount = $query->count();
        Log::info("Groupe {$this->catalogGroup} : {$productsCount} produits à générer");

        $products = $query->get();

        if ($products->isEmpty()) {
            Log::info("Groupe {$this->catalogGroup} vide, ignoré");
            return;
        }

        // Générer le PDF avec options optimisées pour DomPDF
        $pdf = Pdf::loadView(
            'pdf.catalog',
            compact('products'),
        )
        ->setPaper('a4', 'landscape')
        ->setOptions([
            'isHtml5ParserEnabled' => true,
            'isRemoteEnabled' => true,
            'defaultFont' => 'sans-serif',
            'chroot' => [storage_path('app/public'), public_path()],
            'enable_php' => false, // Sécurité
            'isPhpEnabled' => false, // Sécurité
        ]);

        $fileName = 'catalog_' . $this->catalogName . '_group_' . $this->catalogGroup . '.pdf';
        $filePath = storage_path('app/public/pdf/' . $fileName);

        // Créer le répertoire si nécessaire
        if (!is_dir(storage_path('app/public/pdf'))) {
            mkdir(storage_path('app/public/pdf'), 0755, true);
        }

        Log::info("Début génération PDF groupe {$this->catalogGroup}...");
        $pdfStartTime = microtime(true);

        $pdf->save($filePath);

        $pdfElapsed = round(microtime(true) - $pdfStartTime, 2);
        $elapsed = round(microtime(true) - $startTime, 2);
        $productCount = $products->count();
        $memoryUsage = round(memory_get_usage(true) / 1024 / 1024, 2);
        $memoryPeak = round(memory_get_peak_usage(true) / 1024 / 1024, 2);

        Log::info("Groupe {$this->catalogGroup} généré : {$productCount} produits en {$elapsed}s (PDF: {$pdfElapsed}s) | Mémoire: {$memoryUsage}MB (pic: {$memoryPeak}MB)");

        // Libérer la mémoire explicitement
        unset($products, $pdf, $query);
        gc_collect_cycles();
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Échec génération groupe {$this->catalogGroup} : " . $exception->getMessage());
    }
}
