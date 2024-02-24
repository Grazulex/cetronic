<?php

declare(strict_types=1);

namespace App\Actions\User;

use App\DataObjects\User\UserDataObject;
use App\Models\User;

final class UpdateUserAction
{
    public function handle(User $user, UserDataObject $dataObject): User
    {
        $user->update(attributes: $dataObject->toArray());

        return $user;
    }
}
