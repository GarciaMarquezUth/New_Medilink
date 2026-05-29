<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PendingAppointmentService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    public function store(Request $request, PendingAppointmentService $pendingAppointments): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        Role::findOrCreate('paciente', 'web');
        $user->assignRole('paciente');

        event(new Registered($user));

        Auth::login($user);

        $request->session()->regenerate();

        if ($pendingAppointments->hasPending()) {
            $payload = $pendingAppointments->pending();

            try {
                $pendingAppointments->confirmPendingFor($user);
                $request->session()->forget('url.intended');

                return redirect()->route('dashboard')->with('success', PendingAppointmentService::CONFIRMED_MESSAGE);
            } catch (ValidationException) {
                $pendingAppointments->forgetPending();
                $request->session()->forget('url.intended');

                return redirect()->route('portal-citas.index')
                    ->with('error', PendingAppointmentService::UNAVAILABLE_MESSAGE)
                    ->withInput($payload ?? []);
            }
        }

        return redirect(route('dashboard', absolute: false));
    }
}
