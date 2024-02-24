<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\CreateLocationRequest;
use App\Models\Location;
use Illuminate\Http\RedirectResponse;

final class UserController extends Controller
{
    public function locationDelete(Location $location): RedirectResponse
    {
        $location->delete();

        return redirect()
            ->route(route: 'user_locations.list')
            ->with(key: 'success', value: 'Location successfully deleted!');
    }

    public function locationCreate(
        CreateLocationRequest $request
    ): RedirectResponse {
        $user = auth()->user();
        $validated = $request->validated();
        $user->locations()->create($validated);

        return redirect()
            ->route(route: 'user_locations.list')
            ->with(key: 'success', value: 'Location successfully created!');
    }

    public function locationUpdate(
        CreateLocationRequest $request,
        Location $location
    ): RedirectResponse {
        $validated = $request->validated();
        $location->update($validated);

        return redirect()
            ->route(route: 'user_locations.list')
            ->with(key: 'success', value: 'Location successfully updated!');
    }
}
