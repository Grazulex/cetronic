<?php

declare(strict_types=1);

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

final class UpdatePasswordController extends Controller
{
    public function __invoke(Request $request)
    {
        if (
            ! Hash::check(
                $request->get(key: 'current-password'),
                auth()->user()->password,
            )
        ) {
            // The passwords match
            return redirect()
                ->back()
                ->with(
                    key: 'error',
                    value: 'Your current password does not matches with the password.',
                );
        }

        if (
            0 === strcmp(
                $request->get(key: 'current-password'),
                $request->get(key: 'new-password'),
            )
        ) {
            // Current password and new password same
            return redirect()
                ->back()
                ->with(
                    key: 'error',
                    value: 'New Password cannot be same as your current password.',
                );
        }

        $request->validate([
            'current-password' => 'required',
            'new-password' => 'required|string|min:8|confirmed',
        ]);

        //Change Password
        $user = auth()->user();
        $user->password = bcrypt($request->get(key: 'new-password'));
        $user->save();

        return redirect()
            ->back()
            ->with(key: 'success', value: 'Password successfully changed!');
    }
}
