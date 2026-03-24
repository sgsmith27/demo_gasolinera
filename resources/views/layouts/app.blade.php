<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title ?? config('app.name', 'Gasolinera') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-slate-100 text-slate-800">
    <div x-data="{ sidebarOpen: false }" class="min-h-screen flex">
        @auth
            @include('layouts.navigation')
        @endauth

        <div class="flex-1 min-w-0">
            @auth
                <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-4 md:px-6 sticky top-0 z-30">
                    <div class="flex items-center gap-3">
                        <button
                            @click="sidebarOpen = true"
                            class="md:hidden inline-flex items-center justify-center w-10 h-10 rounded-lg border border-slate-200 bg-white text-slate-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            </svg>
                        </button>

                        <div>
                            <h1 class="text-base md:text-lg font-semibold text-slate-800">
                                {{ $title ?? 'Sistema Gasolinera' }}
                            </h1>
                            <p class="hidden sm:block text-xs text-slate-500">
                                Panel de administración
                            </p>
                        </div>
                    </div>

                    <div x-data="{ open:false }" class="relative flex items-center gap-3">
                        <div class="hidden sm:block text-right">
                            <div class="text-sm font-medium text-slate-700">{{ auth()->user()->name }}</div>
                            <div class="text-xs text-slate-500 capitalize">
                                Rol: {{ auth()->user()->role }}
                            </div>
                        </div>

                        <button
                            @click="open = !open"
                            class="w-10 h-10 rounded-full bg-slate-200 flex items-center justify-center text-sm font-semibold text-slate-700 hover:bg-slate-300 transition">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </button>

                        <div
                            x-show="open"
                            @click.outside="open = false"
                            x-transition
                            class="absolute right-0 top-12 w-48 bg-white border border-slate-200 rounded-lg shadow-lg overflow-hidden z-40">

                            <a href="{{ route('profile.edit') }}"
                               class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">
                                Perfil
                            </a>

                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50">
                                    Cerrar sesión
                                </button>
                            </form>
                        </div>
                    </div>
                </header>
            @endauth

            <main class="p-4 md:p-6">
                @isset($slot)
                    {{ $slot }}
                @else
                    @yield('content')
                @endisset
            </main>
        </div>
    </div>
<div id="sessionTimeoutModal" class="hidden fixed inset-0 z-[9999] bg-black/50 items-center justify-center p-4">
    <div class="w-full max-w-md rounded-2xl bg-white shadow-2xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200">
            <h3 class="text-lg font-semibold text-slate-800">Sesión por expirar</h3>
            <p class="text-sm text-slate-500 mt-1">
                Tu sesión se cerrará pronto por inactividad.
            </p>
        </div>

        <div class="px-6 py-5">
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-700">
                Por seguridad, si no realizas ninguna acción serás redirigido al inicio de sesión.
            </div>

            <div class="mt-4 text-sm text-slate-600">
                Tiempo restante:
                <span id="sessionCountdown" class="font-semibold text-slate-800">02:00</span>
            </div>
        </div>

        <div class="px-6 py-4 bg-slate-50 border-t border-slate-200 flex justify-end gap-2">
            <button id="stayLoggedInBtn"
                type="button"
                class="rounded-xl bg-slate-900 text-white px-4 py-2 text-sm font-medium hover:bg-slate-800 transition">
                Continuar en el sistema
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const warningMinutes = 28;
    const logoutMinutes = 30;

    const modal = document.getElementById('sessionTimeoutModal');
    const countdownEl = document.getElementById('sessionCountdown');
    const stayBtn = document.getElementById('stayLoggedInBtn');

    let warningTimeout = null;
    let logoutTimeout = null;
    let countdownInterval = null;
    let countdownSeconds = (logoutMinutes - warningMinutes) * 60;

    function formatTime(seconds) {
        const min = String(Math.floor(seconds / 60)).padStart(2, '0');
        const sec = String(seconds % 60).padStart(2, '0');
        return `${min}:${sec}`;
    }

    function hideModal() {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        clearInterval(countdownInterval);
        countdownInterval = null;
    }

    function showModal() {
        countdownSeconds = (logoutMinutes - warningMinutes) * 60;
        countdownEl.textContent = formatTime(countdownSeconds);

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        clearInterval(countdownInterval);
        countdownInterval = setInterval(() => {
            countdownSeconds--;
            countdownEl.textContent = formatTime(Math.max(countdownSeconds, 0));

            if (countdownSeconds <= 0) {
                clearInterval(countdownInterval);
            }
        }, 1000);
    }

    function goToLogin() {
        window.location.replace('/login?timeout=1&t=' + Date.now());
    }

    function resetTimers() {
        clearTimeout(warningTimeout);
        clearTimeout(logoutTimeout);
        hideModal();

        warningTimeout = setTimeout(() => {
            showModal();
        }, warningMinutes * 60 * 1000);

        logoutTimeout = setTimeout(() => {
            goToLogin();
        }, logoutMinutes * 60 * 1000);
    }

    ['click', 'mousemove', 'keydown', 'scroll', 'touchstart'].forEach(eventName => {
        document.addEventListener(eventName, resetTimers, true);
    });

    stayBtn?.addEventListener('click', function () {
        resetTimers();
    });

    resetTimers();
});
</script>

</body>
</html>