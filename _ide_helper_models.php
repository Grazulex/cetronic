<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\Brand
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property string $description
 * @property string|null $picture
 * @property bool $is_published
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $is_featured
 * @property bool $is_register
 * @property int $order_register
 * @property bool $is_upload_actif
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Item> $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BrandTranslation> $translations
 * @property-read int|null $translations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserBrand> $users
 * @property-read int|null $users_count
 * @method static \Database\Factories\BrandFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Brand newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Brand newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Brand query()
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereIsPublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereIsRegister($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereIsUploadActif($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereOrderRegister($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand wherePicture($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Brand whereUpdatedAt($value)
 */
	class Brand extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\BrandTranslation
 *
 * @property int $id
 * @property int $brand_id
 * @property \App\Enum\TranslationLanguagesEnum $locale
 * @property string $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Brand $brand
 * @method static \Database\Factories\BrandTranslationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|BrandTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BrandTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BrandTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|BrandTranslation whereBrandId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrandTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrandTranslation whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrandTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrandTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BrandTranslation whereUpdatedAt($value)
 */
	class BrandTranslation extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Cart
 *
 * @property int $id
 * @property int $user_id
 * @property \App\Enum\CartStatusEnum $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $cookie
 * @property int|null $shipping_location_id
 * @property int|null $invoice_location_id
 * @property string|null $comment
 * @property-read \App\Models\Location|null $invoiceLocation
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CartItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\Location|null $shippingLocation
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\CartFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Cart newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cart newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Cart query()
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereCookie($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereInvoiceLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereShippingLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Cart whereUserId($value)
 */
	class Cart extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CartItem
 *
 * @property int $id
 * @property int $cart_id
 * @property int $item_id
 * @property int $quantity
 * @property int $price
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $price_promo
 * @property int $price_old
 * @property string|null $variante
 * @property-read \App\Models\Cart $cart
 * @property-read \App\Models\Item $item
 * @method static \Database\Factories\CartItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem whereCartId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem wherePriceOld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem wherePricePromo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartItem whereVariante($value)
 */
	class CartItem extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CartOut
 *
 * @property int $id
 * @property int $user_id
 * @property int $item_id
 * @property int $quantity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Item $item
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|CartOut newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CartOut newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CartOut query()
 * @method static \Illuminate\Database\Eloquent\Builder|CartOut whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartOut whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartOut whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartOut whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartOut whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CartOut whereUserId($value)
 */
	class CartOut extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Category
 *
 * @property int $id
 * @property string $name
 * @property string $slug
 * @property bool $is_published
 * @property string|null $picture
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $is_featured
 * @property int $order
 * @property bool $show_picture_variante
 * @property bool $is_export
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Item> $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CategoryMeta> $metas
 * @property-read int|null $metas_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CategoryTranslation> $translations
 * @property-read int|null $translations_count
 * @method static \Database\Factories\CategoryFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Category newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Category query()
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereIsExport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereIsPublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category wherePicture($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereShowPictureVariante($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereUpdatedAt($value)
 */
	class Category extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CategoryMeta
 *
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $is_color
 * @property bool $is_meta
 * @property bool $is_variante
 * @property bool $is_export
 * @property bool $is_choice
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\CategoryMetaTranslation> $translations
 * @property-read int|null $translations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ItemMeta> $types
 * @property-read int|null $types_count
 * @method static \Database\Factories\CategoryMetaFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMeta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMeta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMeta query()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMeta whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMeta whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMeta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMeta whereIsChoice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMeta whereIsColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMeta whereIsExport($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMeta whereIsMeta($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMeta whereIsVariante($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMeta whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMeta whereUpdatedAt($value)
 */
	class CategoryMeta extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CategoryMetaTranslation
 *
 * @property int $id
 * @property int $category_meta_id
 * @property \App\Enum\TranslationLanguagesEnum $locale
 * @property string $name
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\CategoryMeta $category_meta
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMetaTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMetaTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMetaTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMetaTranslation whereCategoryMetaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMetaTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMetaTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMetaTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMetaTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMetaTranslation whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryMetaTranslation whereUpdatedAt($value)
 */
	class CategoryMetaTranslation extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\CategoryTranslation
 *
 * @property int $id
 * @property int $category_id
 * @property \App\Enum\TranslationLanguagesEnum $locale
 * @property string $name
 * @property string $slug
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Category $category
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryTranslation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryTranslation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryTranslation query()
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryTranslation whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryTranslation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryTranslation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryTranslation whereLocale($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryTranslation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryTranslation whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|CategoryTranslation whereUpdatedAt($value)
 */
	class CategoryTranslation extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Item
 *
 * @property int $id
 * @property string $reference
 * @property string $slug
 * @property string|null $master_reference
 * @property int $brand_id
 * @property int $category_id
 * @property string|null $description
 * @property bool $is_new
 * @property bool $is_published
 * @property int $price
 * @property int $price_b2b
 * @property int $price_promo
 * @property int $price_special1
 * @property int $price_special2
 * @property int $price_special3
 * @property int $multiple_quantity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $master_id
 * @property int $sale_price
 * @property-read \App\Models\Brand $brand
 * @property-read \App\Models\Category $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserDisable> $disables
 * @property-read int|null $disables_count
 * @property-read Item|null $master
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ItemMeta> $metas
 * @property-read int|null $metas_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Item> $variantes
 * @property-read int|null $variantes_count
 * @method static \Illuminate\Database\Eloquent\Builder|Item active()
 * @method static \Database\Factories\ItemFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Item newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Item newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Item query()
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereBrandId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereIsNew($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereIsPublished($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereMasterId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereMasterReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereMultipleQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item wherePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item wherePriceB2b($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item wherePricePromo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item wherePriceSpecial1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item wherePriceSpecial2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item wherePriceSpecial3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereSalePrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Item whereUpdatedAt($value)
 */
	class Item extends \Eloquent implements \Spatie\MediaLibrary\HasMedia {}
}

namespace App\Models{
/**
 * App\Models\ItemMeta
 *
 * @property int $id
 * @property int $meta_id
 * @property string $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $item_id
 * @property-read \App\Models\Item $item
 * @property-read \App\Models\CategoryMeta $meta
 * @method static \Database\Factories\ItemMetaFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|ItemMeta newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ItemMeta newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ItemMeta query()
 * @method static \Illuminate\Database\Eloquent\Builder|ItemMeta whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItemMeta whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItemMeta whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItemMeta whereMetaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItemMeta whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ItemMeta whereValue($value)
 */
	class ItemMeta extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Location
 *
 * @property int $id
 * @property int $user_id
 * @property \App\Enum\LocationTypeEnum $type
 * @property string|null $company
 * @property string|null $firstname
 * @property string|null $lastname
 * @property string|null $vat
 * @property string $street
 * @property string|null $street_number
 * @property string $zip
 * @property string $city
 * @property \App\Enum\CountryEnum $country
 * @property string|null $phone
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $street_other
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\LocationFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Location newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Location newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Location query()
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereFirstname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereLastname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location wherePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereStreet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereStreetNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereStreetOther($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereVat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereZip($value)
 */
	class Location extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Order
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $agent_id
 * @property \App\Enum\OrderStatusEnum $status
 * @property string $reference
 * @property string|null $tracking_number
 * @property string|null $tracking_url
 * @property string|null $shipping_company
 * @property string|null $shipping_name
 * @property string $shipping_street
 * @property string|null $shipping_street_number
 * @property string $shipping_city
 * @property string $shipping_zip
 * @property string $shipping_country
 * @property int $total_price
 * @property int $total_price_with_tax
 * @property int $total_tax
 * @property int $total_shipping
 * @property int $total_shipping_with_tax
 * @property int $total_shipping_tax
 * @property int $total_products
 * @property int $total_products_with_tax
 * @property int $total_products_tax
 * @property string|null $invoice_email
 * @property string|null $invoice_company
 * @property string|null $invoice_name
 * @property string $invoice_street
 * @property string|null $invoice_street_number
 * @property string $invoice_city
 * @property string $invoice_zip
 * @property string $invoice_country
 * @property string|null $invoice_vat
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $shipping_firstname
 * @property string|null $invoice_firstname
 * @property string|null $shipping_lastname
 * @property string|null $invoice_lastname
 * @property int $discount
 * @property string|null $shipping_street_other
 * @property string|null $invoice_street_other
 * @property string|null $comment
 * @property int $franco
 * @property string|null $shipping_phone
 * @property \App\Enum\CountryEnum $country
 * @property-read \App\Models\User|null $agent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\OrderItem> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Order newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Order query()
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereAgentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereDiscount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereFranco($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereInvoiceCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereInvoiceCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereInvoiceCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereInvoiceEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereInvoiceFirstname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereInvoiceLastname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereInvoiceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereInvoiceStreet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereInvoiceStreetNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereInvoiceStreetOther($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereInvoiceVat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereInvoiceZip($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereShippingCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereShippingCompany($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereShippingCountry($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereShippingFirstname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereShippingLastname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereShippingName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereShippingPhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereShippingStreet($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereShippingStreetNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereShippingStreetOther($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereShippingZip($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTotalPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTotalPriceWithTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTotalProducts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTotalProductsTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTotalProductsWithTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTotalShipping($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTotalShippingTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTotalShippingWithTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTotalTax($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTrackingNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereTrackingUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Order whereUserId($value)
 */
	class Order extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\OrderItem
 *
 * @property int $id
 * @property int $order_id
 * @property int $item_id
 * @property string $name
 * @property int $quantity
 * @property int $price_paid
 * @property int $price_show
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $price_old
 * @property-read \App\Models\Item $item
 * @property-read \App\Models\Order $order
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem query()
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem wherePriceOld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem wherePricePaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem wherePriceShow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|OrderItem whereUpdatedAt($value)
 */
	class OrderItem extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \App\Enum\UserRoleEnum $role
 * @property bool $is_actif
 * @property bool $is_blocked
 * @property int $franco
 * @property int $shipping_price
 * @property string|null $external_reference
 * @property int|null $agent_id
 * @property string|null $divers
 * @property bool $receive_cart_notification
 * @property string $language
 * @property \Illuminate\Support\Carbon|null $logged_at
 * @property-read User|null $agent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserBrand> $brands
 * @property-read int|null $brands_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Cart> $carts
 * @property-read int|null $carts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserDisable> $disables
 * @property-read int|null $disables_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\UserDiscount> $discounts
 * @property-read int|null $discounts_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Location> $locations
 * @property-read int|null $locations_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User whereAgentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDivers($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereExternalReference($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFranco($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsActif($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsBlocked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLoggedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereReceiveCartNotification($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereShippingPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 */
	class User extends \Eloquent implements \Filament\Models\Contracts\FilamentUser, \Illuminate\Contracts\Translation\HasLocalePreference {}
}

namespace App\Models{
/**
 * App\Models\UserBrand
 *
 * @property int $id
 * @property int $user_id
 * @property int $brand_id
 * @property int $reduction
 * @property \App\Enum\PriceTypeEnum $price_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $show_promo
 * @property bool $is_excluded
 * @property int|null $category_id
 * @property int $coefficient
 * @property int $addition_price
 * @property bool $not_show_promo
 * @property int|null $category_meta_id
 * @property string|null $category_meta_value
 * @property-read \App\Models\Brand $brand
 * @property-read \App\Models\Category|null $category
 * @property-read \App\Models\CategoryMeta|null $category_meta
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\UserBrandFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|UserBrand newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBrand newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBrand query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserBrand whereAdditionPrice($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBrand whereBrandId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBrand whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBrand whereCategoryMetaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBrand whereCategoryMetaValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBrand whereCoefficient($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBrand whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBrand whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBrand whereIsExcluded($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBrand whereNotShowPromo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBrand wherePriceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBrand whereReduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBrand whereShowPromo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBrand whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserBrand whereUserId($value)
 */
	class UserBrand extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\UserDisable
 *
 * @property int $id
 * @property int $user_id
 * @property int $item_id
 * @property bool $is_enable
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Item $item
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserDisable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserDisable newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserDisable query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserDisable whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDisable whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDisable whereIsEnable($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDisable whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDisable whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDisable whereUserId($value)
 */
	class UserDisable extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\UserDiscount
 *
 * @property int $id
 * @property int $user_id
 * @property int $brand_id
 * @property int|null $category_id
 * @property int $quantity
 * @property int $reduction
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Brand $brand
 * @property-read \App\Models\Category|null $category
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|UserDiscount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserDiscount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserDiscount query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserDiscount whereBrandId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDiscount whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDiscount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDiscount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDiscount whereQuantity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDiscount whereReduction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDiscount whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserDiscount whereUserId($value)
 */
	class UserDiscount extends \Eloquent {}
}

