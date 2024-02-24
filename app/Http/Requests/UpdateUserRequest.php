<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

final class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'receive_cart_notification' => ['sometimes', 'boolean'],
            'name' => ['required', 'string', 'min:3', 'max:255'],
            'language' => ['required', 'string', 'min:2', 'max:5'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => $this->email,
            'name' => $this->name,
            'language' => $this->language,
            'receive_cart_notification' => $this->boolean(key: 'receive_cart_notification'),
        ]);
    }
}
