<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CategoryMeta;
use App\Models\Item;
use App\Models\ItemMeta;
use Illuminate\Database\Seeder;

final class PriceTestMetasSeeder extends Seeder
{
    /**
     * Add metas to our test items.
     */
    public function run(): void
    {
        $testItems = Item::where('reference', 'LIKE', 'TEST-%')->get();
        
        if ($testItems->count() === 0) {
            $this->command->error('Aucun item de test trouvé. Lancez d\'abord PriceTestSeeder');
            return;
        }

        // Récupérer les metas de la catégorie okeefe
        $categoryMetas = CategoryMeta::where('category_id', 1)->get();
        
        if ($categoryMetas->count() === 0) {
            $this->command->error('Aucune meta trouvée pour la catégorie okeefe');
            return;
        }

        $this->command->info('Ajout de metas aux items de test...');

        // Supprimer les anciennes metas des items de test
        ItemMeta::whereIn('item_id', $testItems->pluck('id'))->delete();

        // Valeurs d'exemple pour les metas
        $sampleValues = [
            'colors' => ['#FF0000', '#00FF00', '#0000FF', '#FFFF00', '#FF00FF', '#00FFFF', '#000000'],
            'sizes' => ['S', 'M', 'L', 'XL', 'XXL'],
            'materials' => ['cotton', 'polyester', 'wool', 'silk', 'leather'],
            'general' => ['option1', 'option2', 'option3', 'premium', 'standard', 'basic']
        ];

        foreach ($testItems as $index => $item) {
            $this->command->info("Ajout de metas pour {$item->reference}...");
            
            // Ajouter 3-5 metas par item
            $metasToAdd = $categoryMetas->random(min(5, $categoryMetas->count()));
            
            foreach ($metasToAdd as $metaIndex => $meta) {
                // Choisir une valeur selon le type de meta
                if ($meta->is_color ?? false) {
                    $value = $sampleValues['colors'][$metaIndex % count($sampleValues['colors'])];
                } elseif (str_contains(strtolower($meta->name), 'size')) {
                    $value = $sampleValues['sizes'][$metaIndex % count($sampleValues['sizes'])];
                } elseif (str_contains(strtolower($meta->name), 'material')) {
                    $value = $sampleValues['materials'][$metaIndex % count($sampleValues['materials'])];
                } else {
                    $value = $sampleValues['general'][$metaIndex % count($sampleValues['general'])];
                }

                ItemMeta::create([
                    'item_id' => $item->id,
                    'meta_id' => $meta->id,
                    'value' => $value,
                ]);
            }
        }

        $this->command->info('✅ Metas ajoutées aux items de test');
        
        // Vérification
        foreach ($testItems as $item) {
            $metasCount = $item->fresh()->metas()->count();
            $this->command->info("{$item->reference}: {$metasCount} metas");
        }

        $this->command->info("\n=== URL DE TEST ===");
        $this->command->info("http://localhost:8080/fr/okeefe/category/okeefe?order=price_asc&paginate=50");
    }
}
