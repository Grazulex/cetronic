<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\PdfGeneratorStatusEnum;
use App\Jobs\PdfGenerate;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Model PdfCatalog
 * @property array conditions
 * @property string status
 * @property string url
 * @property string updated_at
 * @property string created_at
 *
 */

class PdfCatalog extends Model
{
    use HasFactory;

    public const CONDITION_BRAND = 'brand';
    public const CONDITION_CATEGORY = 'category';
    public const CONDITION_TYPE = 'type';
    public const CONDITION_GENDER = 'gender';

    public const META_GENDER = 'genre';
    public const META_TYPE = 'type';

    protected $fillable = [
        'conditions',
        'status',
        'url'
    ];

    protected $casts = [
        'conditions' => 'json'
    ];

    public function regenerate()
    {
        $this->status = PdfGeneratorStatusEnum::GENERATING;
        $this->url = null;
        $this->save();
        return $this;
    }

    public function getConditionBrandsAttribute()
    {
        $brands = $this->conditions[self::CONDITION_BRAND];
        $brandNameValue = [];
        foreach ($brands as $brandId) {
            $brandNameValue[] = Brand::find($brandId)->name;
        }
        return $brandNameValue;
    }

    public function getConditionCategoriesAttribute()
    {
        $categories = $this->conditions[self::CONDITION_CATEGORY];
        $categoryNameValue = [];
        foreach ($categories as $categoryId) {
            $categoryNameValue[] = Category::find($categoryId)->name;
        }
        return $categoryNameValue;
    }

    public function getConditionTypesAttribute()
    {
        return $this->conditions[self::CONDITION_TYPE] ?? [];
    }

    public function getConditionGendersAttribute()
    {
        return $this->conditions[self::CONDITION_GENDER] ?? [];
    }

    public function getGeneratedAtAttribute()
    {
        return $this->updated_at !== $this->created_at ? $this->updated_at : null;

    }
    public static function boot(): void
    {
        parent::boot();

        static::saved(function ($model): void {
            if (PdfGeneratorStatusEnum::GENERATED !== $model->status) {
                PdfGenerate::dispatch($model);
            }
        });
    }
}
