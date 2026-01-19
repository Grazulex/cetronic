<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enum\CountryEnum;
use App\Enum\LocationTypeEnum;
use App\Enum\UserRoleEnum;
use App\Models\Location;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rules\Enum;

final class B2bController extends Controller
{
    /**
     * Handle B2B signup request.
     *
     * POST /b2b/signup
     *
     * Required parameters:
     * - key: API key (must match B2B_API_KEY in .env)
     * - name, email, password, company, phone, vat
     * - street, street_number, zip, city, country, language
     */
    public function signup(Request $request): JsonResponse
    {
        // Validate API key
        $apiKey = config('services.b2b.api_key');

        if (empty($apiKey)) {
            Log::error('B2B API: API key not configured');

            return response()->json([
                'success' => false,
                'error' => 'API not configured',
            ], 500);
        }

        if ($request->input('key') !== $apiKey) {
            Log::warning('B2B API: Invalid API key attempt', [
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'success' => false,
                'error' => 'Invalid API key',
            ], 401);
        }

        // Validate input data
        $validator = validator($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'company' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:255'],
            'language' => ['required', 'string', 'max:3'],
            'vat' => ['required', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
            'street_number' => ['required', 'string', 'max:255'],
            'zip' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['required', new Enum(CountryEnum::class)],
            'password' => ['required', 'string', 'min:8'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // Create user (inactive, requires admin validation)
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => Hash::make($request->input('password')),
            'is_actif' => false,
            'language' => $request->input('language'),
            'divers' => 'B2B Registration from: ' . $request->header('Origin', 'unknown'),
            'role' => UserRoleEnum::CUSTOMER->value,
        ]);

        // Create invoice location
        Location::create([
            'user_id' => $user->id,
            'lastname' => $request->input('name'),
            'company' => $request->input('company'),
            'type' => LocationTypeEnum::INVOICE->value,
            'vat' => $request->input('vat'),
            'phone' => $request->input('phone'),
            'street' => $request->input('street'),
            'street_number' => $request->input('street_number'),
            'zip' => $request->input('zip'),
            'city' => $request->input('city'),
            'country' => $request->input('country'),
        ]);

        // Fire registered event (sends notification to admin)
        event(new Registered($user));

        Log::info('B2B API: User registered', [
            'user_id' => $user->id,
            'email' => $user->email,
            'origin' => $request->header('Origin'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'User registered successfully. Account pending validation.',
            'user_id' => $user->id,
        ], 201);
    }
}
