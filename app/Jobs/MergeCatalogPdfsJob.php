<?php

namespace App\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class MergeCatalogPdfsJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes pour la fusion

    /**
     * @param string $catalogName Le nom du catalogue (ex: "Hommes", "Femmes", etc.)
     */
    public function __construct(
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

        $startTime = microtime(true);

        Log::info("Début de la fusion des PDFs pour {$this->catalogName}");

        $pdfDir = storage_path('app/public/pdf/');
        $pattern = $pdfDir . 'catalog_' . $this->catalogName . '_group_*.pdf';
        $pdfFiles = glob($pattern);

        if (empty($pdfFiles)) {
            Log::error("Aucun fichier PDF trouvé pour {$this->catalogName}");
            return;
        }

        // Trier par nom de groupe
        usort($pdfFiles, function($a, $b) {
            preg_match('/group_(\d+)\.pdf$/', $a, $matchA);
            preg_match('/group_(\d+)\.pdf$/', $b, $matchB);
            return ($matchA[1] ?? 0) <=> ($matchB[1] ?? 0);
        });

        Log::info("Fusion de " . count($pdfFiles) . " fichiers pour {$this->catalogName}");

        $finalFileName = 'catalog_' . $this->catalogName . '.pdf';
        $finalPath = $pdfDir . $finalFileName;

        // Tenter avec Imagick d'abord
        if (extension_loaded('imagick')) {
            try {
                $this->mergeWithImagick($pdfFiles, $finalPath);
            } catch (\Exception $e) {
                Log::warning("Échec fusion Imagick, tentative avec CLI : " . $e->getMessage());
                $this->mergeWithCli($pdfFiles, $finalPath);
            }
        } else {
            Log::info("Imagick non disponible, utilisation de pdftk/ghostscript");
            $this->mergeWithCli($pdfFiles, $finalPath);
        }

        // Nettoyer les fichiers temporaires
        foreach ($pdfFiles as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        $elapsed = round(microtime(true) - $startTime, 2);
        Log::info("Fusion terminée pour {$this->catalogName} en {$elapsed}s");
    }

    private function mergeWithImagick(array $pdfFiles, string $outputPath): void
    {
        $imagick = new \Imagick();
        $imagick->setResolution(150, 150); // Résolution raisonnable pour le catalogue

        foreach ($pdfFiles as $pdfFile) {
            $imagick->readImage($pdfFile);
        }

        $imagick->setImageFormat('pdf');
        $imagick->writeImages($outputPath, true);
        $imagick->clear();
        $imagick->destroy();

        Log::info("Fusion réussie avec Imagick");
    }

    private function mergeWithCli(array $pdfFiles, string $outputPath): void
    {
        // Tenter pdftk d'abord
        $command = 'pdftk ' . implode(' ', array_map('escapeshellarg', $pdfFiles)) . ' cat output ' . escapeshellarg($outputPath);

        // Si pdftk n'existe pas, utiliser ghostscript
        if (!shell_exec('which pdftk')) {
            Log::info("pdftk non trouvé, utilisation de ghostscript");
            $command = 'gs -dBATCH -dNOPAUSE -q -sDEVICE=pdfwrite -sOutputFile=' . escapeshellarg($outputPath) . ' ' . implode(' ', array_map('escapeshellarg', $pdfFiles));
        }

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \RuntimeException("Échec de la fusion des PDFs avec CLI");
        }

        Log::info("Fusion réussie avec CLI");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("Échec fusion PDFs pour {$this->catalogName} : " . $exception->getMessage());
    }
}
