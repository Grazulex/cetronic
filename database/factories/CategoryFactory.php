<?php

declare(strict_types=1);

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\File;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
final class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        $name = fake()->domainWord();

        File::makeDirectory('public/storage/categories', 0777, true, true);

        return [
            'name' => $name,
            'slug' => '',
            'is_published' => true,
            'is_featured' => true,
            'picture' => 'categories/' . fake()->image('public/storage/categories', 640, 480, null, false),
        ];
    }
}
