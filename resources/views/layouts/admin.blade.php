<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>APIDIAN - Panel de Administración</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: { 50:'#eff6ff',100:'#dbeafe',200:'#bfdbfe',300:'#93c5fd',400:'#60a5fa',500:'#3b82f6',600:'#2563eb',700:'#1d4ed8',800:'#1e40af',900:'#1e3a5f' },
                        sidebar: '#1e293b',
                    }
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        [x-cloak] { display: none !important; }
        .sidebar-link.active { background: rgba(59,130,246,0.15); color: #60a5fa; border-right: 3px solid #3b82f6; }
    </style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="h-full bg-gray-50">
    <div x-data="{ sidebarOpen: true }" class="flex h-full">
        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'w-64' : 'w-20'" class="fixed inset-y-0 left-0 bg-sidebar text-white transition-all duration-300 z-50 flex flex-col">
            <!-- Logo -->
            <div class="flex items-center justify-between h-16 px-4 border-b border-slate-700">
                <div class="flex items-center gap-3" x-show="sidebarOpen">
                    <div class="w-8 h-8 bg-primary-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-invoice text-white text-sm"></i>
                    </div>
                    <span class="font-bold text-lg">APIDIAN</span>
                </div>
                <div x-show="!sidebarOpen" class="w-full flex justify-center">
                    <div class="w-8 h-8 bg-primary-500 rounded-lg flex items-center justify-center">
                        <i class="fas fa-file-invoice text-white text-sm"></i>
                    </div>
                </div>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <a href="{{ route('home') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-300 hover:bg-slate-700 hover:text-white transition {{ Request::is('home') || Request::is('/') ? 'active' : '' }}">
                    <i class="fas fa-chart-pie w-5 text-center"></i>
                    <span x-show="sidebarOpen">Dashboard</span>
                </a>
                <a href="{{ route('home') }}" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-300 hover:bg-slate-700 hover:text-white transition {{ Request::is('company*') ? 'active' : '' }}">
                    <i class="fas fa-building w-5 text-center"></i>
                    <span x-show="sidebarOpen">Empresas</span>
                </a>
                <a href="{{ route('documentation') }}" target="_blank" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-300 hover:bg-slate-700 hover:text-white transition">
                    <i class="fas fa-code w-5 text-center"></i>
                    <span x-show="sidebarOpen">API Docs</span>
                </a>
                <a href="/api/health/status" target="_blank" class="sidebar-link flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-slate-300 hover:bg-slate-700 hover:text-white transition">
                    <i class="fas fa-heartbeat w-5 text-center"></i>
                    <span x-show="sidebarOpen">Health Check</span>
                </a>
            </nav>

            <!-- User -->
            <div class="p-3 border-t border-slate-700">
                <div class="flex items-center gap-3 px-3 py-2">
                    <div class="w-8 h-8 bg-slate-600 rounded-full flex items-center justify-center text-xs font-bold">
                        {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
                    </div>
                    <div x-show="sidebarOpen" class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-white truncate">{{ Auth::user()->name }}</p>
                        <p class="text-xs text-slate-400 truncate">{{ Auth::user()->email }}</p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div :class="sidebarOpen ? 'ml-64' : 'ml-20'" class="flex-1 transition-all duration-300">
            <!-- Top Bar -->
            <header class="sticky top-0 z-40 bg-white border-b border-gray-200 shadow-sm">
                <div class="flex items-center justify-between h-16 px-6">
                    <div class="flex items-center gap-4">
                        <button @click="sidebarOpen = !sidebarOpen" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-bars text-lg"></i>
                        </button>
                        <h1 class="text-lg font-semibold text-gray-800">@yield('title', 'Dashboard')</h1>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-sm text-gray-500">{{ now()->format('d M, Y') }}</span>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="text-sm text-gray-500 hover:text-red-600 transition flex items-center gap-2">
                                <i class="fas fa-sign-out-alt"></i>
                                <span class="hidden sm:inline">Salir</span>
                            </button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-6">
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
