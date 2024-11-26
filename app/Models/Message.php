<?php

declare(strict_types=1);

namespace App\Models;

use App\Enum\TranslationLanguagesEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'start_date',
        'end_date',
        'local',
        'title',
        'content',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('start_date', '<=', now())
            ->where('end_date', '>=', now());
    }

    public function scopeLocal($query, string $local)
    {
        return $query->where('local', $local);
    }
}
