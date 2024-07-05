<?php

declare(strict_types=1);

namespace App\Services;

use App\Enum\CartStatusEnum;
use App\Enum\CountryEnum;
use App\Enum\LocationTypeEnum;
use App\Models\Brand;
use App\Models\Cart;
use App\Models\Category;
use App\Models\User;
use App\Models\UserBrand;
use App\Models\UserDiscount;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

final readonly class UserService
{
    public function __construct(private User $user) {}

    public function getAllMetasExcluded(): Collection
    {
        return UserBrand::where('user_id', $this->user->id)
            ->where('is_excluded', true)
            ->where('category_meta_value', '<>', null)
            ->pluck('category_meta_value');
    }

    public function getAllBrandAndCategoryRules(Brand $brand, ?Category $category = null): null|Collection
    {
        if ($category) {
            if ( ! $rule = UserBrand::where('user_id', $this->user->id)->where('brand_id', $brand->id)->where('category_id', $category->id)->get()) {
                $rule = UserBrand::where('user_id', $this->user->id)->where('brand_id', $brand->id)->get();
            }

            return $rule;
        }
        return UserBrand::where('user_id', $this->user->id)->where('brand_id', $brand->id)->get();

    }

    public function getAllBrandAndCategoryDiscounts(Brand $brand, Category $category): null|Collection
    {
        return UserDiscount::where('user_id', $this->user->id)->where('brand_id', $brand->id)->where('category_id', $category->id)->orderBy('quantity')->get();
    }

    public function getBrandAndCategoryDiscounts(Brand $brand, Category $category, int $quantity): null|UserDiscount
    {
        return UserDiscount::where('user_id', $this->user->id)->where('brand_id', $brand->id)->where('category_id', $category->id)->where('quantity', '<=', $quantity)->orderBy('quantity', 'desc')->first();
    }

    public function getOpenCart(?string $cookie = null): Cart|null
    {
        if ($cookie) {
            $guest = (new GuestService())->getGuestUser();

            return Cart::where('user_id', $guest->id)->where('cookie', $cookie)->where('status', CartStatusEnum::OPEN->value)->first();
        }

        return Cart::where('user_id', $this->user->id)->where('status', CartStatusEnum::OPEN->value)->first();
    }

    public function getFixedShippingPrice(): int
    {
        return (int) $this->user->shipping_price;
    }

    public function getFranco(): int
    {
        return (int) $this->user->franco;
    }

    public function hasVAT(): bool
    {
        $locations = $this->user->locations;
        foreach ($locations as $location) {
            if (LocationTypeEnum::INVOICE === $location->type && $location->country->value === CountryEnum::BELGIUM->value) {
                return true;
            }
        }

        return false;
    }

    public function getPdf(): Response
    {

        $pdf = Pdf::loadView('pdf/user', ['user' => $this->user]);

        return $pdf->stream();
    }
}
