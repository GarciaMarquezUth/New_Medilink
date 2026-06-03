<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class GmailApiService
{
    public function configuredForOAuth(): bool
    {
        return filled(config('services.gmail.client_id'))
            && filled(config('services.gmail.client_secret'))
            && filled(config('services.gmail.redirect_uri'));
    }

    public function configuredForSending(): bool
    {
        return $this->configuredForOAuth()
            && filled(config('services.gmail.refresh_token'))
            && filled(config('services.gmail.from_address'));
    }

    public function authorizationUrl(string $state): string
    {
        if (! $this->configuredForOAuth()) {
            throw new RuntimeException('Falta configurar las credenciales OAuth de Gmail.');
        }

        return 'https://accounts.google.com/o/oauth2/v2/auth?'.http_build_query([
            'client_id' => config('services.gmail.client_id'),
            'redirect_uri' => config('services.gmail.redirect_uri'),
            'response_type' => 'code',
            'scope' => config('services.gmail.scope'),
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ], '', '&', PHP_QUERY_RFC3986);
    }

    public function exchangeCodeForToken(string $code): array
    {
        if (! $this->configuredForOAuth()) {
            throw new RuntimeException('Falta configurar las credenciales OAuth de Gmail.');
        }

        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('services.gmail.client_id'),
            'client_secret' => config('services.gmail.client_secret'),
            'redirect_uri' => config('services.gmail.redirect_uri'),
            'grant_type' => 'authorization_code',
            'code' => $code,
        ]);

        if ($response->failed()) {
            throw new RuntimeException('Google no devolvio tokens validos: '.$response->body());
        }

        return $response->json();
    }

    public function send(string $to, string $subject, string $html, string $text): ?string
    {
        if (! $this->configuredForSending()) {
            throw new RuntimeException('Falta configurar Gmail para enviar correos.');
        }

        $response = Http::withToken($this->accessToken())
            ->post('https://gmail.googleapis.com/gmail/v1/users/me/messages/send', [
                'raw' => $this->base64UrlEncode($this->mimeMessage($to, $subject, $html, $text)),
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Gmail no pudo enviar el correo: '.$response->body());
        }

        return $response->json('id');
    }

    private function accessToken(): string
    {
        $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'client_id' => config('services.gmail.client_id'),
            'client_secret' => config('services.gmail.client_secret'),
            'refresh_token' => config('services.gmail.refresh_token'),
            'grant_type' => 'refresh_token',
        ]);

        if ($response->failed() || blank($response->json('access_token'))) {
            throw new RuntimeException('Google no devolvio un access token valido: '.$response->body());
        }

        return $response->json('access_token');
    }

    private function mimeMessage(string $to, string $subject, string $html, string $text): string
    {
        $boundary = 'clinic-'.Str::random(32);
        $fromName = (string) config('services.gmail.from_name');
        $fromAddress = (string) config('services.gmail.from_address');

        $headers = [
            'From: '.$this->formatAddress($fromName, $fromAddress),
            'To: '.$this->sanitizeHeader($to),
            'Subject: '.$this->encodeHeader($subject),
            'Date: '.now()->toRfc7231String(),
            'MIME-Version: 1.0',
            'Content-Type: multipart/alternative; boundary="'.$boundary.'"',
        ];

        return implode("\r\n", $headers)
            ."\r\n\r\n--{$boundary}\r\n"
            ."Content-Type: text/plain; charset=UTF-8\r\n"
            ."Content-Transfer-Encoding: quoted-printable\r\n\r\n"
            .quoted_printable_encode($text)
            ."\r\n\r\n--{$boundary}\r\n"
            ."Content-Type: text/html; charset=UTF-8\r\n"
            ."Content-Transfer-Encoding: quoted-printable\r\n\r\n"
            .quoted_printable_encode($html)
            ."\r\n\r\n--{$boundary}--";
    }

    private function formatAddress(string $name, string $address): string
    {
        $address = $this->sanitizeHeader($address);
        $name = trim(str_replace(['"', '\\'], '', $this->sanitizeHeader($name)));

        if ($name === '') {
            return $address;
        }

        return $this->encodeHeader($name).' <'.$address.'>';
    }

    private function encodeHeader(string $value): string
    {
        return mb_encode_mimeheader($this->sanitizeHeader($value), 'UTF-8', 'B', "\r\n");
    }

    private function sanitizeHeader(string $value): string
    {
        return str_replace(["\r", "\n"], '', $value);
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
