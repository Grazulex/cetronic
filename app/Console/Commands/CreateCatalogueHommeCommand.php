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
    public $timeout = 0; // Pas de timeout
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-catalogue-homme
                            {--skip-merge : Ne pas fusionner les PDFs (garde les fichiers séparés)}
                            {--merge-only : Fusionner uniquement les PDFs existants}
                            {--resume= : Reprendre à partir d\'un groupe spécifique}
                            {--only= : Générer uniquement certains groupes (ex: 010,020,030)}';
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
        set_time_limit(0);
        ini_set('max_execution_time', '0');

        // Mode fusion uniquement
        if ($this->option('merge-only')) {
            $this->info('Mode fusion uniquement...');
            $this->mergeExistingPdfs();
            return;
        }

        $this->info('Début de la génération du catalogue...');

        // Récupérer les groupes uniques pour traiter par section
        $catalogGroups = Item::where('is_published', 1)
            ->whereNotNull('catalog_group')
            ->distinct()
            ->orderBy('catalog_group')
            ->pluck('catalog_group');

        // Filtrer selon les options
        if ($only = $this->option('only')) {
            $onlyGroups = explode(',', $only);
            $catalogGroups = $catalogGroups->filter(fn($g) => in_array($g, $onlyGroups));
            $this->info("Mode filtré : " . count($catalogGroups) . " groupes sélectionnés");
        }

        if ($resume = $this->option('resume')) {
            $resumeIndex = $catalogGroups->search($resume);
            if ($resumeIndex !== false) {
                $catalogGroups = $catalogGroups->slice($resumeIndex);
                $this->info("Reprise à partir du groupe : {$resume}");
            }
        }

        if ( ! Storage::directories('public/pdf')) {
            Storage::makeDirectory('public/pdf');
        }

        $pdfFiles = [];
        $groupCount = $catalogGroups->count();
        $currentGroup = 0;

        // Générer un PDF par groupe de catalogue
        foreach ($catalogGroups as $group) {
            $currentGroup++;
            $startTime = microtime(true);

            $this->info("Traitement du groupe {$currentGroup}/{$groupCount}: {$group}");

            // Vérifier si le fichier existe déjà (pour reprise)
            $fileName = self::FILE_NAME_PREFIX . 'group_' . $group . self::FILE_NAME_POSTFIX;
            $filePath = storage_path(self::STORAGE_PDF_DIR . $fileName);

            if (file_exists($filePath) && $this->option('resume')) {
                $this->warn("  → Fichier existant, ignoré");
                $pdfFiles[] = $filePath;
                continue;
            }

            // Charger uniquement les produits de ce groupe
            $products = Item::where('is_published', 1)
                ->where('catalog_group', $group)
                ->select(['id', 'reference', 'catalog_group', 'reseller_price'])
                ->with([
                    'brand:id,name',
                    'metas' => fn($q) => $q->select(['id', 'item_id', 'meta_id', 'value'])->with('meta:id,name'),
                    'media'
                ])
                ->orderBy('reference')
                ->get();

            if ($products->isEmpty()) {
                $this->warn("  → Aucun produit, ignoré");
                continue;
            }

            $pdf = Pdf::loadView(
                'pdf.catalog',
                compact('products'),
            )->setPaper('a4', 'landscape');

            $pdf->save($filePath);
            $pdfFiles[] = $filePath;

            $elapsed = round(microtime(true) - $startTime, 2);
            $memoryUsage = round(memory_get_usage(true) / 1024 / 1024, 2);

            // Libérer la mémoire
            unset($products, $pdf);
            gc_collect_cycles();

            $this->info("  ✓ Généré en {$elapsed}s | Mémoire: {$memoryUsage}MB");
        }

        // Fusionner tous les PDFs en un seul (sauf si --skip-merge)
        if (!$this->option('skip-merge')) {
            $this->info('Fusion des PDFs...');
            $this->mergePdfs($pdfFiles);

            // Nettoyer les fichiers temporaires
            $this->info('Nettoyage des fichiers temporaires...');
            foreach ($pdfFiles as $file) {
                if (file_exists($file)) {
                    unlink($file);
                }
            }
        } else {
            $this->info('PDFs conservés séparément (--skip-merge)');
            $this->info('Pour fusionner plus tard : php artisan app:create-catalogue-homme --merge-only');
        }

        $this->info('✓ Catalogue généré avec succès !');
    }

    /**
     * Fusionne les PDFs existants par groupe
     */
    private function mergeExistingPdfs(): void
    {
        $pdfDir = storage_path(self::STORAGE_PDF_DIR);
        $pattern = $pdfDir . self::FILE_NAME_PREFIX . 'group_*.pdf';
        $pdfFiles = glob($pattern);

        if (empty($pdfFiles)) {
            $this->error('Aucun fichier PDF de groupe trouvé à fusionner');
            return;
        }

        // Trier par nom de groupe
        usort($pdfFiles, function($a, $b) {
            preg_match('/group_(\d+)\.pdf$/', $a, $matchA);
            preg_match('/group_(\d+)\.pdf$/', $b, $matchB);
            return ($matchA[1] ?? 0) <=> ($matchB[1] ?? 0);
        });

        $this->info(count($pdfFiles) . ' fichiers à fusionner...');
        $this->mergePdfs($pdfFiles);
        $this->info('✓ Fusion terminée !');
    }

    /**
     * Fusionne plusieurs fichiers PDF en un seul
     */
    private function mergePdfs(array $pdfFiles): void
    {
        $finalFileName = self::FILE_NAME_PREFIX . 'Full' . self::FILE_NAME_POSTFIX;
        $finalPath = storage_path(self::STORAGE_PDF_DIR . $finalFileName);

        // Utiliser pdftk ou ghostscript pour fusionner
        // pdftk est plus léger en mémoire
        $command = 'pdftk ' . implode(' ', array_map('escapeshellarg', $pdfFiles)) . ' cat output ' . escapeshellarg($finalPath);

        // Alternative avec ghostscript si pdftk n'est pas disponible
        if (! shell_exec('which pdftk')) {
            $this->warn('pdftk non trouvé, utilisation de ghostscript...');
            $command = 'gs -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite -sOutputFile=' . escapeshellarg($finalPath) . ' ' . implode(' ', array_map('escapeshellarg', $pdfFiles));
        }

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            $this->error('Erreur lors de la fusion des PDFs');
            throw new \RuntimeException('Échec de la fusion des PDFs');
        }
    }
}
