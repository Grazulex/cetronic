<?php

declare(strict_types=1);

namespace App\Console\Commands;

class CreateCatalogueUnisexCommand extends AbstractCatalogueCommand
{
    protected $signature = 'app:create-catalogue-unisex
                            {--skip-merge : Ne pas fusionner les PDFs (garde les fichiers séparés)}
                            {--merge-only : Fusionner uniquement les PDFs existants}
                            {--resume= : Reprendre à partir d\'un groupe spécifique}
                            {--only= : Générer uniquement certains groupes (ex: 010,020,030)}';

    protected $description = 'Génère le catalogue pour les produits sans genre (Unisex)';

    protected function getFileName(): string
    {
        return 'Unisex';
    }

    protected function getGenderFilter(): ?string
    {
        // null = produits sans genre spécifique
        return null;
    }
}
