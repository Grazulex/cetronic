<?php

declare(strict_types=1);

namespace App\Services;

use App\Enum\PriceTypeEnum;
use App\Models\Item;
use App\Models\ItemMeta;
use App\Models\User;
use App\Models\UserBrand;
use Illuminate\Database\Eloquent\Collection;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;

final class ItemService
{
    public function __construct(private Item $item) {}

    public function getPrice(?User $user = null): \Illuminate\Support\Collection
    {
        if ($user) {
            return $this->getPricesForUser($user);
        }
        return $this->getPricesForGuest();

    }

    public function getPictures(): MediaCollection
    {
        return $this->item->getMedia();
    }

    public function getPromoItems(int $qty = 8): Collection
    {
        return Item::where('price_promo', '>', 0)
            ->with('brand')
            ->with('category')
            ->with('variants')
            ->enable(auth()->user())
            ->inRandomOrder()
            ->limit(8)
            ->get();
    }

    public function getNewItems(int $qty = 8): Collection
    {
        return Item::where('is_new', true)
            ->with('brand')
            ->with('category')
            ->with('variants')
            ->enable(auth()->user())
            ->inRandomOrder()
            ->limit($qty)
            ->get();
    }

    public function isNew()
    {
        return $this->item->is_new;
    }

    public function getPicturesZip(Item $item): ?string
    {

        return $item->getFirstMediaPath();
    }

    public function getDefaultPicturePdf(): string
    {
        return $this->item->getFirstMediaPath();
    }

    public function getDefaultPicture(): string
    {
        return $this->item->getFirstMediaUrl();
    }

    public function getVariantIfChoice()
    {
        if (0 === count($this->item->variants)) {
            $metas = $this->item->metas;
            if ($metas) {
                foreach ($metas as $meta) {
                    if ($meta->meta->is_choice) {
                        $values = ItemMeta::where('item_id', $this->item->id)->where('meta_id', $meta->meta->id)->get();
                        if (1 === count($values)) {
                            return $values->first()->value;
                        }
                    }
                }
            }
        }
        return null;
    }

    private function getPricesForUser(User $user): \Illuminate\Support\Collection
    {
        if ( ! $rule = UserBrand::where('user_id', $user->id)
            ->where('brand_id', $this->item->brand_id)
            ->where('category_id', $this->item->category_id)
            ->whereIn('category_meta_id', $this->item->metas->pluck('meta_id'))
            ->whereIn('category_meta_value', $this->item->metas->pluck('value'))
            ->first()) {
            $rule = UserBrand::where('user_id', $user->id)
                ->where('brand_id', $this->item->brand_id)
                ->where('category_id', $this->item->category_id)
                ->whereNull('category_meta_id')
                ->whereNull('category_meta_value')
                ->first();
        };
        if ($rule) {
            $price_end = 0;
            $price_promo = ($rule->not_show_promo) ? 0 : $this->item->price_promo;
            $price_start = match ($rule->price_type) {
                PriceTypeEnum::B2B => $this->item->price_b2b,
                PriceTypeEnum::SPECIAL_1 => $this->item->price_special1,
                PriceTypeEnum::SPECIAL_2 => $this->item->price_special2,
                PriceTypeEnum::SPECIAL_3 => $this->item->price_special3,
                PriceTypeEnum::FIX => $this->item->price_fix,
                default => $this->getDefaultPrice(),
            };
            if (0.0 === (float) $price_start) {
                $price_start = $this->getDefaultPrice();
            }

            if($rule->addition_price > 0 && PriceTypeEnum::FIX !== $rule->price_type) {
                $price_start += $rule->addition_price;
                $price_promo = ($price_promo > 0) ? $price_promo + $rule->addition_price : 0;
            }

            if($rule->coefficient > 0 && PriceTypeEnum::FIX !== $rule->price_type) {
                $price_start *= $rule->coefficient;
                $price_promo = ($price_promo > 0) ? $price_promo * $rule->coefficient : 0;
            }

            if($rule->reduction > 0 && PriceTypeEnum::FIX !== $rule->price_type) {
                $price_end = ($price_promo > 0) ? $price_promo - ($price_promo * ($rule->reduction / 100)) : $price_start - ($price_start * ($rule->reduction / 100));
            }


            return collect([
                'price_start' => $price_start,
                'price_promo' => $price_promo,
                'price_end'   => ($price_end > 0) ? $price_end : (($price_promo > 0) ? $price_promo : $price_start),
                'sale'        => $this->item->sale_price,
            ]);
        }
        return collect([
            'price_start' => $this->getDefaultPrice(),
            'price_promo' => $this->item->price_promo,
            'price_end'   => ($this->item->price_promo > 0) ? $this->item->price_promo : $this->getDefaultPrice(),
            'sale'        => $this->item->sale_price,
        ]);

    }

    private function getDefaultPrice(): float
    {
        if (auth()->check()) {
            if (( ! $this->item->price) || (0 === $this->item->price)) {
                return $this->item->price_b2b;
            }
        }

        return $this->item->price;
    }

    private function getPricesForGuest(): \Illuminate\Support\Collection
    {
        return collect([
            'price_start' => $this->getDefaultPrice(),
            'price_promo' => $this->item->price_promo,
            'price_end'   => ($this->item->price_promo > 0) ? $this->item->price_promo : $this->getDefaultPrice(),
            'sale'        => 0,
        ]);
    }
}
