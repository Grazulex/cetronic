<?php

declare(strict_types=1);

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Facade;

return [

    'available_locales' => [
        'en',
        'fr',
        'nl'
    ],


    'aliases' => Facade::defaultAliases()->merge([
        'Image' => Intervention\Image\Facades\Image::class,

        // 'ExampleClass' => App\Example\ExampleClass::class,
    ])->toArray(),

];
