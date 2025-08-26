<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CustomerType;
use Illuminate\Database\Seeder;

class CustomerTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Bijoutier',
                'description' => 'Bijoutier professionnel',
                'is_active' => true,
            ],
            [
                'name' => 'Grossiste',
                'description' => 'Grossiste en bijouterie',
                'is_active' => true,
            ],
            [
                'name' => 'Détaillant',
                'description' => 'Commerce de détail',
                'is_active' => true,
            ],
            [
                'name' => 'Horloger',
                'description' => 'Horloger professionnel',
                'is_active' => true,
            ],
            [
                'name' => 'Agent',
                'description' => 'Agent commercial',
                'is_active' => true,
            ],
        ];

        foreach ($types as $type) {
            CustomerType::firstOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}
