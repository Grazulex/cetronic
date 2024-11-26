<?php

declare(strict_types=1);

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Brand;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\CategoryMeta;
use App\Models\Item;
use App\Models\ItemMeta;
use App\Models\Location;
use App\Models\Message;
use App\Models\User;
use App\Services\OrderService;
use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

final class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run(): void
    {
        $folders = ['items', 'brands', 'catergories', 'orders'];
        foreach ($folders as $folder) {
            if (Storage::directoryExists(storage_path($folder))) {
                Storage::deleteDirectory(storage_path($folder));
            }
        }
        Message::factory(10)->create();
        User::factory(1)->create([
            'email' => 'jms@grazulex.be',
            'name' => 'admin',
            'password' => bcrypt('password'),
        ]);
        User::factory(2)->create();
        Brand::factory(10)->create();
        Category::factory(5)->create();

        $user = User::find(1);
        Location::factory(1)->create([
            'user_id' => $user->id,
            'type' => 'shipping',
        ]);
        Location::factory(1)->create([
            'user_id' => $user->id,
            'type' => 'invoice',
        ]);

        Cart::factory(1)->create([
            'user_id' => $user->id,
            'shipping_location_id' => 1,
            'invoice_location_id' => 2,
        ]);

        $user = User::find(2);
        Location::factory(1)->create([
            'user_id' => $user->id,
            'type' => 'shipping',
        ]);
        Location::factory(1)->create([
            'user_id' => $user->id,
            'type' => 'invoice',
        ]);

        Cart::factory(2)->create([
            'user_id' => $user->id,
            'shipping_location_id' => 3,
            'invoice_location_id' => 4,
        ]);

        $categories = Category::all();
        foreach ($categories as $category) {
            CategoryMeta::factory(10)->create([
                'category_id' => $category->id,
            ]);

            Item::factory(25)->create([
                'category_id' => $category->id,
            ]);

            $items = Item::where('category_id', $category->id)->get();
            foreach ($items as $item) {
                $metas = CategoryMeta::where(
                    'category_id',
                    $category->id,
                )->get();
                foreach ($metas as $meta) {
                    if ($meta->is_color) {
                        $color = $this->getRandomColor();
                        ItemMeta::factory()->create([
                            'item_id' => $item->id,
                            'meta_id' => $meta->id,
                            'value' => $color,
                        ]);
                    } else {
                        ItemMeta::factory()->create([
                            'item_id' => $item->id,
                            'meta_id' => $meta->id,
                        ]);
                    }
                }

                $faker = Factory::create();
                $imageUrl = $faker->imageUrl(640, 480, null, false);

                $item->addMediaFromUrl($imageUrl)
                    ->toMediaCollection('default', 'items');
            }
        }

        for ($i = 1; $i <= 4; $i++) {
            $master = Item::where('category_id', $i)
                ->get()
                ->random();
            $items = Item::whereNull('master_id')
                ->where('category_id', $i)
                ->whereNot('id', $master->id)
                ->limit(5)
                ->get();
            foreach ($items as $item) {
                $item->master_id = $master->id;
                $item->save();
            }
        }

        CartItem::factory(5)->create(['cart_id' => 1]);

        CartItem::factory(5)->create(['cart_id' => 2]);

        CartItem::factory(10)->create(['cart_id' => 3]);

        $orderService = new OrderService();
        $orderService->create(Cart::find(3));
    }

    public function getRandomColor()
    {
        return '#' .
            str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
    }
}
