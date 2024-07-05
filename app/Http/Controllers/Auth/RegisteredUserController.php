<?php

declare(strict_types=1);

namespace App\Http\Controllers\Auth;

use App\Enum\CountryEnum;
use App\Enum\LocationTypeEnum;
use App\Enum\UserRoleEnum;
use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Location;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rules\Enum;

final class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @param  Request  $request
     * @return \Illuminate\Http\RedirectResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'company' => ['string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone' => ['required', 'string', 'max:255'],
            'language' => ['required', 'string', 'max:3'],
            'vat' => ['required', 'string', 'max:255'],
            'street' => ['required', 'string', 'max:255'],
            'street_number' => ['required', 'string', 'max:255'],
            'zip' => ['required', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'country' => ['required', new Enum(CountryEnum::class)],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        if (empty($request->get(key: 'title'))) {

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'is_actif' => false,
                'language' => $request->language,
                'divers' => 'Registration infos. Is a old customer: ' . (($request->customer && 'yes' === $request->customer) ? 'yes' : (($request->customer && 'no' === $request->customer) ? 'no' : 'unknown')) . '. Interested in: ' . (is_array($request->brands) ? implode(',', $request->brands) : '--'),
                'role' => UserRoleEnum::CUSTOMER->value,
            ]);

            Location::create([
                'user_id' => $user->id,
                'lastname' => $request->name,
                'company' => $request->company,
                'type' => LocationTypeEnum::INVOICE->value,
                'vat' => $request->vat,
                'phone' => $request->phone,
                'street' => $request->street,
                'street_number' => $request->street_number,
                'zip' => $request->zip,
                'city' => $request->city,
                'country' => $request->country,
            ]);


            event(new Registered($user));

            //Auth::login($user);

            return redirect(route('user_thanks'))->with('status', __('user.auth.registered.waiting'));
        }
        return redirect(route('user_thanks'))->with('status', __('user.auth.registered.error'));

    }

    /**
     * Display the registration view.
     *
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $brands = Brand::orderBy('order_register')->where('is_register', true)->get();
        $countries = CountryEnum::cases();


        return view('auth.register', compact('brands', 'countries'));
    }
}
