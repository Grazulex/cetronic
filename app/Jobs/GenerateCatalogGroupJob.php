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

    public $timeout = 300; // 5 minutes max par groupe
    public $queue = 'catalog'; // Queue dédiée pour le suivi dans Horizon

    /**
     * @param string $catalogGroup Le numéro du groupe (ex: "010", "015", etc.)
     * @param string $genderFilter Le filtre de genre ("Gents", "Ladies", "Kids", null pour unisex)
     * @param string $catalogName Le nom du catalogue (ex: "Hommes", "Femmes", etc.)
     */
    public function __construct(
        public string $catalogGroup,
        public ?string $genderFilter,
        public string $catalogName
    ) {}

    public function handle(): void
    {
        if ($this->batch()?->cancelled()) {
            return;
        }

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

        // Eager load relations
        $query->with([
            'brand:id,name',
            'metas' => fn($q) => $q->select(['id', 'item_id', 'meta_id', 'value'])->with('meta:id,name'),
            'media'
        ])->orderBy('reference');

        $products = $query->get();

        if ($products->isEmpty()) {
            Log::info("Groupe {$this->catalogGroup} vide, ignoré");
            return;
        }

        // Générer le PDF
        $pdf = Pdf::loadView(
            'pdf.catalog',
            compact('products'),
        )->setPaper('a4', 'landscape');

        $fileName = 'catalog_' . $this->catalogName . '_group_' . $this->catalogGroup . '.pdf';
        $filePath = storage_path('app/public/pdf/' . $fileName);

        // Créer le répertoire si nécessaire
        if (!is_dir(storage_path('app/public/pdf'))) {
            mkdir(storage_path('app/public/pdf'), 0755, true);
        }

        $pdf->save($filePath);

        $elapsed = round(microtime(true) - $startTime, 2);
        $productCount = $products->count();
        $memoryUsage = round(memory_get_usage(true) / 1024 / 1024, 2);

        Log::info("Groupe {$this->catalogGroup} généré : {$productCount} produits en {$elapsed}s | Mémoire: {$memoryUsage}MB");

        // Libérer la mémoire
        unset($products, $pdf, $query);
        gc_collect_cycles();
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Échec génération groupe {$this->catalogGroup} : " . $exception->getMessage());
    }
}
