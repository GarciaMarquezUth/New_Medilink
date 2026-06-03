<?php

namespace App\Http\Controllers;

use App\Services\GmailApiService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use RuntimeException;

class GmailOAuthController extends Controller
{
    public function redirect(GmailApiService $gmail)
    {
        $state = Str::random(40);
        session()->put('gmail_oauth_state', $state);

        return redirect()->away($gmail->authorizationUrl($state));
    }

    public function callback(Request $request, GmailApiService $gmail)
    {
        if ($request->filled('error')) {
            return view('Gmail.callback', [
                'refreshToken' => null,
                'error' => $request->query('error'),
            ]);
        }

        if ($request->query('state') !== session()->pull('gmail_oauth_state')) {
            abort(403, 'Estado OAuth invalido.');
        }

        if (! $request->filled('code')) {
            throw new RuntimeException('Google no devolvio codigo OAuth.');
        }

        $tokens = $gmail->exchangeCodeForToken($request->query('code'));

        return view('Gmail.callback', [
            'refreshToken' => $tokens['refresh_token'] ?? null,
            'error' => null,
        ]);
    }
}
