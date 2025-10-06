<?php

namespace App\Console\Commands;

use App\Jobs\GenerateCatalogGroupJob;
use App\Jobs\MergeCatalogPdfsJob;
use App\Models\Item;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Bus;

class CreateCatalogueCommand extends Command
{
    protected $signature = 'app:create-catalogue
                            {--queue : Utiliser les queues pour la génération (recommandé sur Forge)}';

    protected $description = 'Génère le catalogue complet de tous les produits';

    public function handle(): void
    {
        $this->info("🚀 Génération du catalogue complet");

        // Récupérer TOUS les groupes de catalogue publiés
        $catalogGroups = Item::where('is_published', 1)
            ->whereNotNull('catalog_group')
            ->distinct()
            ->orderBy('catalog_group')
            ->pluck('catalog_group');

        if ($catalogGroups->isEmpty()) {
            $this->error('❌ Aucun groupe de catalogue trouvé');
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

            $this->error('❌ Mode synchrone désactivé pour éviter les timeouts.');
            $this->info('   Utilisez: php artisan app:create-catalogue --queue');
        }
    }

    /**
     * Dispatch les jobs dans une batch Laravel
     */
    private function dispatchJobs($catalogGroups): void
    {
        $this->info("📤 Création des jobs...");

        $jobs = [];

        // Créer un job par groupe (TOUS les produits du groupe, peu importe le genre)
        foreach ($catalogGroups as $group) {
            $jobs[] = new GenerateCatalogGroupJob(
                catalogGroup: $group,
                genderFilter: null, // null = TOUS les genres
                catalogName: 'Full'
            );
        }

        // Créer un batch avec tous les jobs + le job de merge à la fin
        $batch = Bus::batch($jobs)
            ->then(function () {
                // Une fois tous les jobs terminés, lancer le merge sur la queue catalog
                MergeCatalogPdfsJob::dispatch('Full')->onQueue('catalog');
            })
            ->catch(function () {
                \Log::error("Échec de la génération du catalogue complet");
            })
            ->finally(function () {
                \Log::info("Batch de génération du catalogue terminé");
            })
            ->name("Catalogue Complet")
            ->onQueue('catalog') // La batch doit aussi spécifier la queue
            ->dispatch();

        $this->info("✅ Batch créé avec ID: {$batch->id}");
        $this->info("📊 {$catalogGroups->count()} jobs de génération + 1 job de fusion");
        $this->line("");
        $this->info("🔍 Suivre la progression dans Horizon:");
        $this->line("   " . config('app.url') . '/horizon');
        $this->line("   Batch ID: {$batch->id}");
        $this->line("");
        $this->info("📄 Le PDF final sera disponible à:");
        $this->line("   storage/app/public/pdf/catalog_Full.pdf");
    }
}
