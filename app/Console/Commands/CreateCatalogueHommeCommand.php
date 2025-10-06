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
    public $timeout = 9000;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-catalogue-homme';
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
        $this->info('Début de la génération du catalogue...');

        // Récupérer les groupes uniques pour traiter par section
        $catalogGroups = Item::where('is_published', 1)
            ->whereNotNull('catalog_group')
            ->distinct()
            ->orderBy('catalog_group')
            ->pluck('catalog_group');

        if ( ! Storage::directories('public/pdf')) {
            Storage::makeDirectory('public/pdf');
        }

        $pdfFiles = [];
        $groupCount = $catalogGroups->count();
        $currentGroup = 0;

        // Générer un PDF par groupe de catalogue
        foreach ($catalogGroups as $group) {
            $currentGroup++;
            $this->info("Traitement du groupe {$currentGroup}/{$groupCount}: {$group}");

            // Charger uniquement les produits de ce groupe
            $products = Item::where('is_published', 1)
                ->where('catalog_group', $group)
                ->with(['brand', 'metas', 'variants', 'media'])
                ->orderBy('reference')
                ->get();

            $pdf = Pdf::loadView(
                'pdf.catalog',
                compact('products'),
            )->setPaper('a4', 'landscape');

            $fileName = self::FILE_NAME_PREFIX . 'group_' . $group . self::FILE_NAME_POSTFIX;
            $filePath = storage_path(self::STORAGE_PDF_DIR . $fileName);

            $pdf->save($filePath);
            $pdfFiles[] = $filePath;

            // Libérer la mémoire
            unset($products, $pdf);
            gc_collect_cycles();

            $this->info("✓ Groupe {$group} généré");
        }

        // Fusionner tous les PDFs en un seul
        $this->info('Fusion des PDFs...');
        $this->mergePdfs($pdfFiles);

        // Nettoyer les fichiers temporaires
        foreach ($pdfFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        $this->info('✓ Catalogue complet généré avec succès !');
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
