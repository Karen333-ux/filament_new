@php
    $botUsername = config('services.telegram.bot_username');
@endphp

@if (filled($botUsername))
    <div class="tg-login">
        @if (session('telegram_error'))
            <p class="tg-login-error">{{ session('telegram_error') }}</p>
        @endif

        <div class="tg-login-divider"><span>{{ __('or') }}</span></div>

        {{-- Telegram renders its own button into this container. The widget
             posts back to data-auth-url as a signed GET, which is why the
             callback route is a plain web route and not a panel route. --}}
        <div class="tg-login-widget">
            <script
                async
                src="https://telegram.org/js/telegram-widget.js?22"
                data-telegram-login="{{ $botUsername }}"
                data-size="large"
                data-radius="8"
                data-auth-url="{{ route('telegram.login.callback') }}"
                data-request-access="write"
            ></script>
        </div>
    </div>

    <style>
        .tg-login { margin-top: 1.5rem; }

        .tg-login-error {
            margin-bottom: 1rem;
            border-radius: 0.5rem;
            background-color: rgb(254 242 242);
            padding: 0.625rem 0.75rem;
            font-size: 0.875rem;
            line-height: 1.25rem;
            color: rgb(153 27 27);
        }

        .tg-login-divider {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            font-size: 0.75rem;
            line-height: 1rem;
            color: rgb(113 113 122);
        }

        .tg-login-divider::before,
        .tg-login-divider::after {
            content: '';
            height: 1px;
            flex: 1 1 0%;
            background-color: rgb(228 228 231);
        }

        .tg-login-widget {
            display: flex;
            justify-content: center;
            margin-top: 1rem;
            min-height: 2.5rem;
        }

        .dark .tg-login-error {
            background-color: rgb(69 10 10);
            color: rgb(254 202 202);
        }

        .dark .tg-login-divider { color: rgb(161 161 170); }

        .dark .tg-login-divider::before,
        .dark .tg-login-divider::after { background-color: rgb(63 63 70); }
    </style>
@endif
