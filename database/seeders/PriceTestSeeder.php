<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Database\Seeder;

final class PriceTestSeeder extends Seeder
{
    /**
     * Seed the application's database with price test scenarios.
     */
    public function run(): void
    {
        // Pour l'URL /fr/bradtke/category/bradtke, on a besoin de:
        // - catSlug = "bradtke" (filtre par catégorie)
        // - type = "category", slug = "bradtke" (affiche la catégorie bradtke)
        // Donc tous les items doivent être dans la catégorie "bradtke"
        
        $targetCategory = Category::where('slug', 'bradtke')->first();
        $brands = Brand::limit(5)->get(); // Prendre plusieurs marques différentes

        if (!$targetCategory || $brands->count() == 0) {
            $this->command->error('Catégorie bradtke ou marques non trouvées');
            return;
        }

        $this->command->info("Création d'items de test dans la catégorie: {$targetCategory->slug}");
        $this->command->info("Avec différentes marques pour tester");

        // Supprimer les anciens items de test
        Item::where('reference', 'LIKE', 'TEST-%')->delete();

        // Scénario 1: Prix normal (pas de promo) - Brand 1
        Item::create([
            'reference' => 'TEST-001',
            'slug' => 'test-001-prix-normal',
            'brand_id' => $brands[0]->id,
            'category_id' => $targetCategory->id,
            'description' => 'Test: Prix normal 100€ - ' . $brands[0]->slug,
            'is_published' => true,
            'price' => 10000, // 100€ en centimes
            'price_promo' => 0,
            'price_b2b' => 9000,
        ]);

        // Scénario 2: Prix avec promo PLUS AVANTAGEUSE - Brand 2
        Item::create([
            'reference' => 'TEST-002',
            'slug' => 'test-002-promo-avantageuse',
            'brand_id' => $brands[1]->id,
            'category_id' => $targetCategory->id,
            'description' => 'Test: Prix 120€, Promo 80€ - ' . $brands[1]->slug,
            'is_published' => true,
            'price' => 12000, // 120€
            'price_promo' => 8000, // 80€ (plus avantageux)
            'price_b2b' => 11000,
        ]);

        // Scénario 3: Prix avec promo MOINS AVANTAGEUSE - Brand 3
        Item::create([
            'reference' => 'TEST-003',
            'slug' => 'test-003-promo-moins-avantageuse',
            'brand_id' => $brands[2]->id,
            'category_id' => $targetCategory->id,
            'description' => 'Test: Prix 90€, Promo 110€ - ' . $brands[2]->slug,
            'is_published' => true,
            'price' => 9000, // 90€
            'price_promo' => 11000, // 110€ (moins avantageux)
            'price_b2b' => 8500,
        ]);

        // Scénario 4: Prix très bas pour être en tête - Brand 4
        Item::create([
            'reference' => 'TEST-004',
            'slug' => 'test-004-prix-tres-bas',
            'brand_id' => $brands[3]->id,
            'category_id' => $targetCategory->id,
            'description' => 'Test: Prix très bas 50€ - ' . $brands[3]->slug,
            'is_published' => true,
            'price' => 5000, // 50€
            'price_promo' => 0,
            'price_b2b' => 4500,
        ]);

        // Scénario 5: Prix élevé avec grosse promo - Brand 5 ou 1
        Item::create([
            'reference' => 'TEST-005',
            'slug' => 'test-005-grosse-promo',
            'brand_id' => $brands[4]->id ?? $brands[0]->id,
            'category_id' => $targetCategory->id,
            'description' => 'Test: Prix 200€, Promo 60€ - ' . ($brands[4]->slug ?? $brands[0]->slug),
            'is_published' => true,
            'price' => 20000, // 200€
            'price_promo' => 6000, // 60€ (grosse promo)
            'price_b2b' => 18000,
        ]);

        // Scénario 6: Prix moyen - Brand 2
        Item::create([
            'reference' => 'TEST-006',
            'slug' => 'test-006-prix-moyen',
            'brand_id' => $brands[1]->id,
            'category_id' => $targetCategory->id,
            'description' => 'Test: Prix moyen 75€ - ' . $brands[1]->slug,
            'is_published' => true,
            'price' => 7500, // 75€
            'price_promo' => 0,
            'price_b2b' => 7000,
        ]);

        // Scénario 7: Prix avec promo égale - Brand 3
        Item::create([
            'reference' => 'TEST-007',
            'slug' => 'test-007-promo-egale',
            'brand_id' => $brands[2]->id,
            'category_id' => $targetCategory->id,
            'description' => 'Test: Prix 85€, Promo 85€ - ' . $brands[2]->slug,
            'is_published' => true,
            'price' => 8500, // 85€
            'price_promo' => 8500, // 85€ (égal)
            'price_b2b' => 8000,
        ]);

        $this->command->info('✅ 7 items de test créés avec différents scénarios de prix dans différentes marques');
        
        // Afficher les marques utilisées
        $this->command->info("\n=== MARQUES UTILISÉES ===");
        foreach ($brands as $index => $brand) {
            $this->command->info("Brand {$index}: {$brand->slug} (ID: {$brand->id})");
        }
        
        // Afficher l'ordre attendu
        $this->command->info("\n=== ORDRE ATTENDU (prix effectif croissant) ===");
        $this->command->info("1. TEST-004: 50€ (pas de promo)");
        $this->command->info("2. TEST-005: 60€ (promo de 200€)");
        $this->command->info("3. TEST-006: 75€ (pas de promo)");
        $this->command->info("4. TEST-002: 80€ (promo de 120€)");
        $this->command->info("5. TEST-007: 85€ (promo égale)");
        $this->command->info("6. TEST-003: 90€ (promo moins avantageuse ignorée)");
        $this->command->info("7. TEST-001: 100€ (pas de promo)");
        
        $this->command->info("\n=== URL DE TEST ===");
        $this->command->info("http://localhost:8080/fr/okeefe/category/okeefe?order=price_asc&paginate=50");
    }
}
