<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Jobs\GenerateCatalogGroupJob;
use App\Jobs\MergeCatalogPdfsJob;
use App\Models\Item;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Bus;

abstract class AbstractCatalogueCommand extends Command
{
    protected $signature = '{--queue : Utiliser les queues pour la génération (recommandé sur Forge)}';

    /**
     * Retourne le nom du fichier final (ex: "Hommes", "Femmes", "Enfants", "Unisex")
     */
    abstract protected function getFileName(): string;

    /**
     * Retourne le filtre de genre (meta_id = 1), null pour "sans genre"
     */
    abstract protected function getGenderFilter(): ?string;

    public function handle(): void
    {
        $this->info("🚀 Génération du catalogue : {$this->getFileName()}");

        // Construire la requête de base avec filtre de genre
        $baseQuery = Item::where('is_published', 1)
            ->whereNotNull('catalog_group');

        // Appliquer le filtre de genre
        $genderFilter = $this->getGenderFilter();
        if ($genderFilter !== null) {
            $baseQuery->whereHas('metas', function (Builder $q) use ($genderFilter): void {
                $q->where('value', $genderFilter)->where('meta_id', 1);
            });
        } else {
            // Pour les produits sans genre : exclure Gents, Ladies, Kids
            $baseQuery->whereDoesntHave('metas', function (Builder $q): void {
                $q->where('meta_id', 1)
                    ->whereIn('value', ['Gents', 'Ladies', 'Kids']);
            });
        }

        // Récupérer les groupes uniques pour ce genre
        $catalogGroups = (clone $baseQuery)
            ->distinct()
            ->orderBy('catalog_group')
            ->pluck('catalog_group');

        if ($catalogGroups->isEmpty()) {
            $this->error('❌ Aucun groupe de catalogue trouvé pour ce genre');
            return;
        }

        $this->info("📊 {$catalogGroups->count()} groupes trouvés");

        // Si option --queue, utiliser les jobs
        if ($this->option('queue')) {
            $this->dispatchJobs($catalogGroups);
        } else {
            $this->warn('⚠️  Mode synchrone non recommandé sur Forge (timeout 5min)');
            $this->warn('   Utilisez --queue pour éviter les timeouts');

            if (!$this->confirm('Continuer en mode synchrone ?', false)) {
                $this->info('Annulé. Relancez avec --queue');
                return;
            }

            $this->error('Mode synchrone désactivé. Utilisez --queue');
        }
    }

    /**
     * Dispatch les jobs dans une batch Laravel
     */
    private function dispatchJobs($catalogGroups): void
    {
        $this->info("📤 Création des jobs...");

        $jobs = [];

        // Créer un job par groupe
        foreach ($catalogGroups as $group) {
            $jobs[] = new GenerateCatalogGroupJob(
                catalogGroup: $group,
                genderFilter: $this->getGenderFilter(),
                catalogName: $this->getFileName()
            );
        }

        // Créer un batch avec tous les jobs + le job de merge à la fin
        $batch = Bus::batch($jobs)
            ->then(function () {
                // Une fois tous les jobs terminés, lancer le merge
                MergeCatalogPdfsJob::dispatch($this->getFileName());
            })
            ->catch(function () {
                \Log::error("Échec de la génération du catalogue {$this->getFileName()}");
            })
            ->finally(function () {
                \Log::info("Batch terminé pour {$this->getFileName()}");
            })
            ->name("Catalogue {$this->getFileName()}")
            ->onQueue('default')
            ->dispatch();

        $this->info("✅ Batch créé avec ID: {$batch->id}");
        $this->info("📊 {$catalogGroups->count()} jobs de génération + 1 job de fusion");
        $this->info("🔍 Suivre la progression dans Horizon");
        $this->line("");
        $this->line("   Horizon URL: " . config('app.url') . '/horizon');
        $this->line("   Batch ID: {$batch->id}");
    }
}
