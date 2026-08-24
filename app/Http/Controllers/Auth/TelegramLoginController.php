<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TelegramLoginController extends Controller
{
    /**
     * How long a signed Telegram payload stays usable. The signature never
     * expires on its own, so without this window a captured callback URL
     * would be a permanent login link for that account.
     */
    protected const MAX_AUTH_AGE = 86400;

    public function __invoke(Request $request): RedirectResponse
    {
        $botToken = config('services.telegram.bot_token');

        if (blank($botToken)) {
            return $this->failed('Telegram login is not configured.');
        }

        $payload = $request->query();

        if (! $this->signatureIsValid($payload, $botToken)) {
            return $this->failed('Could not verify this Telegram login.');
        }

        // The signature proves the payload came from our bot, not that it
        // carries every field we go on to read.
        $telegramId = $payload['id'] ?? null;

        if (blank($telegramId)) {
            return $this->failed('This Telegram login was missing an account id.');
        }

        if ((int) ($payload['auth_date'] ?? 0) < now()->timestamp - self::MAX_AUTH_AGE) {
            return $this->failed('This Telegram login has expired. Please try again.');
        }

        $user = $this->resolveUser($payload, $telegramId);

        if (! $user) {
            return $this->failed("No account is linked to this Telegram profile (id {$telegramId}).");
        }

        Auth::guard(Filament::getAuthGuard())->login($user, remember: true);

        $request->session()->regenerate();

        return redirect()->intended(Filament::getUrl());
    }

    /**
     * Verify the payload against the recipe in Telegram's login-widget docs:
     * every field except `hash`, sorted by key, joined as `key=value` lines,
     * signed with HMAC-SHA256 under a key of SHA256(bot token).
     */
    protected function signatureIsValid(array $payload, string $botToken): bool
    {
        $hash = $payload['hash'] ?? null;

        if (! is_string($hash)) {
            return false;
        }

        unset($payload['hash']);
        ksort($payload);

        $checkString = collect($payload)
            ->map(fn ($value, string $key): string => "{$key}={$value}")
            ->implode("\n");

        $expected = hash_hmac('sha256', $checkString, hash('sha256', $botToken, true));

        return hash_equals($expected, $hash);
    }

    protected function resolveUser(array $payload, string $telegramId): ?User
    {
        $user = User::query()->where('telegram_id', $telegramId)->first();

        if ($user) {
            $user->forceFill([
                'telegram_username' => $payload['username'] ?? null,
                'avatar_url' => $payload['photo_url'] ?? null,
            ])->save();

            return $user;
        }

        if (! config('services.telegram.auto_register')) {
            return null;
        }

        return User::create([
            'telegram_id' => $telegramId,
            'telegram_username' => $payload['username'] ?? null,
            'avatar_url' => $payload['photo_url'] ?? null,
            'name' => $this->displayName($payload),
        ]);
    }

    protected function displayName(array $payload): string
    {
        $name = trim(($payload['first_name'] ?? '').' '.($payload['last_name'] ?? ''));

        return $name ?: ($payload['username'] ?? 'Telegram user');
    }

    protected function failed(string $message): RedirectResponse
    {
        return redirect()
            ->to(Filament::getLoginUrl())
            ->with('telegram_error', $message);
    }
}
