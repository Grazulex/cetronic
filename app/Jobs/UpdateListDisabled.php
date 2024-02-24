<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Models\UserBrand;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

final class UpdateListDisabled implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(public UserBrand $userBrand)
    {
    }


    public function handle(): void
    {
        $Items = $this->userBrand->brand->items()->where('category_id', $this->userBrand->category_id)->get();
        foreach ($Items as $item) {
            if ($this->userBrand->is_excluded) {
                $item->disables()->updateOrCreate([
                    'user_id' => $this->userBrand->user_id,
                ]);
            } else {
                $item->disables()->where('user_id', $this->userBrand->user_id)->delete();
            }
        }
    }
}
