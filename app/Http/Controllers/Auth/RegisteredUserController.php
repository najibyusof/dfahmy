<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Guest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'phone_number' => ['required', 'string', 'max:30'],
            'identification_number' => ['required', 'string', 'max:100'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($request): User {
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
            ]);

            $guest = Guest::query()->where('email', $user->email)->lockForUpdate()->first();
            if ($guest !== null && $guest->user_id !== null) {
                throw ValidationException::withMessages(['email' => 'This email is already linked to a guest account.']);
            }

            if ($guest === null) {
                Guest::query()->create([
                    'user_id' => $user->id,
                    'full_name' => $user->name,
                    'email' => $user->email,
                    'phone_number' => $request->phone_number,
                    'identification_number' => $request->identification_number,
                ]);
            } else {
                if (! hash_equals((string) $guest->phone_number, (string) $request->phone_number)
                    || ! hash_equals((string) $guest->identification_number, (string) $request->identification_number)) {
                    throw ValidationException::withMessages([
                        'email' => 'These details do not match the existing guest booking record.',
                    ]);
                }

                $guest->update(['user_id' => $user->id]);
            }

            return $user;
        });

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('guest.portal')->with('status', 'account-created');
    }
}
