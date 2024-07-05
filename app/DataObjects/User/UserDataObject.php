<?php

declare(strict_types=1);

namespace App\DataObjects\User;

use App\Http\Requests\UpdateUserRequest;

final readonly class UserDataObject
{
    public function __construct(
        private UpdateUserRequest $request,
    ) {}

    public function toArray(): array
    {
        return [
            'email' => $this->request['email'],
            'receive_cart_notification' => $this->request['receive_cart_notification'],
            'name' => $this->request['name'],
            'language' => $this->request['language'],
        ];
    }
}
