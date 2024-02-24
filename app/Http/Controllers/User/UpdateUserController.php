<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Actions\User\UpdateUserAction;
use App\DataObjects\User\UserDataObject;
use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use Illuminate\Http\RedirectResponse;

final class UpdateUserController extends Controller
{
    public function __invoke(UpdateUserRequest $request): RedirectResponse
    {
        $request->validated();

        $user = (new UpdateUserAction())->handle(
            auth()->user(),
            new UserDataObject(
                request: $request,
            )
        );
        return redirect()
            ->back()
            ->with(key: 'success', value: 'profile successfully updated!');
    }
}
