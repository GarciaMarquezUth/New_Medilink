<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\PatientProfileService;
use App\Services\PendingAppointmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('Inicio-de-sesion.login');
    }

    public function login(Request $request, PendingAppointmentService $pendingAppointments, PatientProfileService $patientProfiles)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            if ($patientProfiles->requiresCompletion($request->user())) {
                $patientProfiles->ensurePatientFor($request->user(), $pendingAppointments->pending() ?? []);
                $request->session()->flash('status', PatientProfileService::INCOMPLETE_MESSAGE);

                return redirect()->route('pacientes.profile');
            }

            if ($pendingAppointments->hasPending()) {
                $payload = $pendingAppointments->pending();

                try {
                    $pendingAppointments->confirmPendingFor($request->user());
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

            return redirect()->intended('/dashboard');
        }

        return back()->withErrors([
            'email' => 'Las credenciales proporcionadas no coinciden con nuestros registros.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
