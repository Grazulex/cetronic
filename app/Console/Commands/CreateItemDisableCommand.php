<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Item;
use App\Models\UserBrand;
use Illuminate\Console\Command;

final class CreateItemDisableCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-item-disable-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        $rules = UserBrand::where('is_excluded', true)->get();
        foreach ($rules as $rule) {
            $items = Item::where('brand_id', $rule->brand_id)->where('category_id', $rule->category_id)->get();
            foreach ($items as $item) {
                $item->disables()->create([
                    'user_id' => $rule->user_id,
                ]);
            }
        }
    }
}
