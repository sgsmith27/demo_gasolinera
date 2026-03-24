@php
    $dashboardUrl = auth()->user()->role === 'despachador' ? url('/cashier-dashboard') : route('dashboard');
    $dashboardActive = request()->routeIs('dashboard') || request()->is('cashier-dashboard');
@endphp

<div class="md:flex md:self-stretch">
    <!-- Overlay móvil -->
    <div
        x-show="sidebarOpen"
        x-transition.opacity
        class="fixed inset-0 bg-slate-900/50 z-40 md:hidden"
        @click="sidebarOpen = false">
    </div>

    <!-- Sidebar -->
    <aside
    class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-900 text-slate-100 border-r border-slate-800 transform transition-transform duration-300
           md:translate-x-0 md:static md:z-auto md:flex md:flex-col md:min-h-screen md:h-auto"
        :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'">

        <div class="h-16 flex items-center justify-between px-6 border-b border-slate-800">
            <a href="{{ $dashboardUrl }}" class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-dark-500 flex items-center justify-center text-white font-bold">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="w-10 h-10 rounded-xl">
                </div>
                <div>
                    <div class="text-sm font-semibold text-white">Gasolinera D-R San Juan</div>
                    <div class="text-xs text-slate-400">Sistema administrativo</div>
                </div>
            </a>

            <button
                @click="sidebarOpen = false"
                class="md:hidden inline-flex items-center justify-center w-9 h-9 rounded-lg text-slate-300 hover:bg-slate-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto px-4 py-5">
            <div class="space-y-1">
                <div class="text-[11px] uppercase tracking-wider text-slate-500 px-3 mb-2">Principal</div>

                <a href="{{ $dashboardUrl }}"
                   @click="sidebarOpen = false"
                   class="flex items-center rounded-lg px-3 py-2 text-sm font-medium transition {{ $dashboardActive ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                    Dashboard
                </a>

                @auth
                    <a href="{{ url('/sales/new') }}"
                       @click="sidebarOpen = false"
                       class="flex items-center rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->is('sales/new') ? 'bg-orange-500 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                        Nueva venta
                    </a>

                    @if(in_array(auth()->user()->role, ['admin', 'supervisor']))
                        <div class="text-[11px] uppercase tracking-wider text-slate-500 px-3 mt-5 mb-2">Operación</div>

                        <a href="{{ url('/inventory') }}"
                           @click="sidebarOpen = false"
                           class="flex items-center rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->is('inventory') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Inventario
                        </a>

                        <a href="{{ url('/work-shifts') }}"
                            @click="sidebarOpen = false"
                            class="flex items-center rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->is('work-shifts') || request()->is('work-shifts/*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                Turnos
                            </a>

                        <a href="{{ url('/fuel-deliveries') }}"
                           @click="sidebarOpen = false"
                           class="flex items-center rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->is('fuel-deliveries') && !request()->is('fuel-deliveries/new') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Abastecimientos
                        </a>

                        <a href="{{ url('/fuel-deliveries/new') }}"
                           @click="sidebarOpen = false"
                           class="flex items-center rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->is('fuel-deliveries/new') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Abastecer combustible
                        </a>

                        <a href="{{ url('/inventory-adjustments/new') }}"
                           @click="sidebarOpen = false"
                           class="flex items-center rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->is('inventory-adjustments/new') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Ajuste inventario
                        </a>

                        <a href="{{ url('/customers') }}"
                                    @click="sidebarOpen = false"
                                    class="flex items-center rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->is('customers') || request()->is('customers/*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                                        Clientes
                                    </a>

                        <a href="{{ url('/accounts-receivable') }}"
                        @click="sidebarOpen = false"
                        class="flex items-center rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->is('accounts-receivable') || request()->is('accounts-receivable/*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Cuentas por cobrar
                        </a>
                        
                        <a href="{{ url('/suppliers') }}"
                        @click="sidebarOpen = false"
                        class="flex items-center rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->is('suppliers') || request()->is('suppliers/*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Proveedores
                        </a>

                        <a href="{{ url('/accounts-payable') }}"
                        @click="sidebarOpen = false"
                        class="flex items-center rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->is('accounts-payable') || request()->is('accounts-payable/*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Cuentas por pagar
                        </a>

                        <div class="text-[11px] uppercase tracking-wider text-slate-500 px-3 mt-5 mb-2">Configuración</div>

                        <a href="{{ url('/fuel-prices') }}"
                           @click="sidebarOpen = false"
                           class="flex items-center rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->is('fuel-prices') || request()->is('fuel-prices/*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Precios
                        </a>

                        <a href="{{ url('/pumps') }}"
                           @click="sidebarOpen = false"
                           class="flex items-center rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->is('pumps') || request()->is('pumps/*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Bombas
                        </a>

                        <a href="{{ url('/expenses') }}"
                           @click="sidebarOpen = false"
                           class="flex items-center rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->is('expenses') || request()->is('expenses/*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Gastos
                        </a>

                        <a href="{{ url('/reports') }}"
                           @click="sidebarOpen = false"
                           class="flex items-center rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->is('reports') || request()->is('reports/*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Reportes
                        </a>
                    @endif

                    @if(auth()->user()->role === 'admin')
                        <div class="text-[11px] uppercase tracking-wider text-slate-500 px-3 mt-5 mb-2">Administración</div>

                        <a href="{{ url('/users') }}"
                           @click="sidebarOpen = false"
                           class="flex items-center rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->is('users') || request()->is('users/*') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Usuarios
                        </a>
                        <a href="{{ url('/audit-logs') }}"
                        @click="sidebarOpen = false"
                        class="flex items-center rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->is('audit-logs') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Bitácora
                        </a>

                        <a href="{{ url('/audit-logs/dashboard') }}"
                        @click="sidebarOpen = false"
                        class="flex items-center rounded-lg px-3 py-2 text-sm font-medium transition {{ request()->is('audit-logs/dashboard') ? 'bg-slate-800 text-white' : 'text-slate-300 hover:bg-slate-800 hover:text-white' }}">
                            Dashboard auditoría
                        </a>
                    @endif
                @endauth
            </div>
        </div>
    </aside>
</div>