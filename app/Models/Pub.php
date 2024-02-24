<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

final class Pub extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'title',
        'type',
        'title',
        'url',
        'language',
        'picture',
    ];

    protected $casts = [

    ];
}
