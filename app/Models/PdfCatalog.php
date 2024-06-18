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

    const META_GENDER = 'genre';
    const META_TYPE = 'type';

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
        return $this->updated_at != $this->created_at ? $this->updated_at : null;

    }
    public static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            if ($model->status != PdfGeneratorStatusEnum::GENERATED) {
                PdfGenerate::dispatch($model);
            }
        });
    }
}
