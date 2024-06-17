<?php

namespace App\Models;

use App\Enum\PdfGeneratorStatusEnum;
use App\Jobs\PdfGenerate;
use Carbon\Carbon;
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

    const CONDITION_BRAND = 'brand';
    const CONDITION_TYPE = 'type';
    const CONDITION_GENDER= 'gender';

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
        $this->status = PdfGeneratorStatusEnum::PENDING;
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

    public function getConditionTypesAttribute()
    {
        $types = $this->conditions[self::CONDITION_TYPE];
        $typeNameValue = [];
        foreach ($types as $categoryId) {
            $typeNameValue[] = Category::find($categoryId)->name;
        }
        return $typeNameValue;
    }

    public function getConditionGendersAttribute()
    {
        return $this->conditions[self::CONDITION_GENDER] ?? [];
    }

    public function getGeneratedAtAttribute()
    {
        return $this->updated_at != $this->created_at ? $this->updated_at : null;

    }
    public static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            if ($model->status === PdfGeneratorStatusEnum::PENDING) {
                PdfGenerate::dispatch($model);
            }
        });
    }
}
