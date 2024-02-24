<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enum\CountryEnum;
use App\Enum\LocationTypeEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

final class CreateLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', new Enum(type: LocationTypeEnum::class)],
            'company' => ['required', 'string', 'min:3', 'max:255'],
            'firstname' => ['required', 'string', 'min:3', 'max:255'],
            'lastname' => ['required', 'string', 'min:3', 'max:255'],
            'vat' => ['required', 'string', 'min:3', 'max:255'],
            'street' => ['required', 'string', 'min:3', 'max:255'],
            'street_number' => ['sometimes','max:255'],
            'street_other' => ['sometimes', 'max:255'],
            'zip' => ['required', 'string', 'min:3', 'max:255'],
            'city' => ['required', 'string', 'min:3', 'max:255'],
            'country' => ['required', new Enum(type: CountryEnum::class)],
            'phone' => ['required', 'string', 'min:3', 'max:255'],
        ];
    }
}
