<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Traitor.dev')</title>
    <link rel="icon" href="/favicon.svg" type="image/svg+xml">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: { 50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af' },
                        sidebar: { bg: '#1c2434', hover: '#333a48', active: '#333a48', text: '#dee4ee', muted: '#8a99af' },
                    }
                }
            }
        }
    </script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="h-full bg-gray-50 text-gray-900 antialiased">
    <div class="flex h-full">
        {{-- Sidebar --}}
        <aside id="sidebar" class="fixed inset-y-0 left-0 z-30 w-64 bg-sidebar-bg transform -translate-x-full transition-transform duration-200 ease-in-out lg:translate-x-0 lg:static lg:inset-auto flex flex-col">
            {{-- Logo --}}
            <div class="flex items-center gap-2 px-6 py-5 border-b border-white/10">
                <div class="h-8 w-8 rounded-lg bg-brand-600 flex items-center justify-center">
                    <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75 22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3-4.5 16.5" /></svg>
                </div>
                <a href="/" class="text-xl font-bold text-white tracking-tight">traitor<span class="text-brand-500">.dev</span></a>
            </div>

            {{-- Nav --}}
            <nav class="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                <p class="px-3 mb-2 text-xs font-semibold uppercase tracking-wider text-sidebar-muted">Menu</p>

                <a href="/" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->is('/') ? 'bg-sidebar-active text-white' : 'text-sidebar-text hover:bg-sidebar-hover' }} transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" /></svg>
                    Dashboard
                </a>

                <a href="/team" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->is('team') ? 'bg-sidebar-active text-white' : 'text-sidebar-text hover:bg-sidebar-hover' }} transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
                    Team
                </a>

                <a href="/sites" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->is('sites') ? 'bg-sidebar-active text-white' : 'text-sidebar-text hover:bg-sidebar-hover' }} transition">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5a17.92 17.92 0 0 1-8.716-2.247m0 0A9.015 9.015 0 0 1 3 12c0-1.605.42-3.113 1.157-4.418" /></svg>
                    Sites
                </a>

                @foreach(\App\Models\Site::orderBy('name')->get() as $navSite)
                    <a href="/sites/{{ $navSite->id }}" class="flex items-center gap-3 pl-11 pr-3 py-1.5 rounded-lg text-sm {{ request()->is('sites/' . $navSite->id) ? 'text-white bg-sidebar-active' : 'text-sidebar-muted hover:text-sidebar-text hover:bg-sidebar-hover' }} transition truncate">
                        <span class="truncate flex-1">{{ $navSite->name }}</span>
                        @if($navSite->maintenance_mode)
                            <span class="h-1.5 w-1.5 rounded-full bg-amber-400 flex-shrink-0" title="Maintenance mode"></span>
                        @endif
                    </a>
                @endforeach

                @if(auth()->user()->is_admin)
                    <p class="px-3 mt-6 mb-2 text-xs font-semibold uppercase tracking-wider text-sidebar-muted">Admin</p>
                    <a href="{{ route('admin.logs') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->is('admin/logs') ? 'bg-sidebar-active text-white' : 'text-sidebar-text hover:bg-sidebar-hover' }} transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12h16.5m-16.5 3.75h16.5M3.75 19.5h16.5M5.625 4.5h12.75a1.875 1.875 0 0 1 0 3.75H5.625a1.875 1.875 0 0 1 0-3.75Z" /></svg>
                        Logs
                    </a>
                    <a href="{{ route('admin.emails') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->is('admin/emails*') ? 'bg-sidebar-active text-white' : 'text-sidebar-text hover:bg-sidebar-hover' }} transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                        Emails
                    </a>
                    <a href="{{ route('admin.settings') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium {{ request()->is('admin/settings') ? 'bg-sidebar-active text-white' : 'text-sidebar-text hover:bg-sidebar-hover' }} transition">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                        Settings
                    </a>
                @endif
            </nav>

            {{-- Footer --}}
            <div class="px-6 py-4 border-t border-white/10">
                <a href="{{ route('profile') }}" class="block hover:opacity-80 transition">
                    <p class="text-xs text-sidebar-text font-medium truncate">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-sidebar-muted truncate">{{ auth()->user()->email }}</p>
                </a>
                <div class="flex items-center justify-between mt-2">
                    <a href="{{ route('profile') }}" class="text-xs text-sidebar-muted hover:text-sidebar-text transition">Profile</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="text-xs text-sidebar-muted hover:text-sidebar-text transition">Sign out</button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Sidebar overlay (mobile) --}}
        <div id="sidebar-overlay" class="fixed inset-0 z-20 bg-black/50 hidden lg:hidden" onclick="toggleSidebar()"></div>

        {{-- Main content --}}
        <div class="flex-1 flex flex-col min-h-screen lg:min-h-0 overflow-auto">
            {{-- Top bar --}}
            <header class="sticky top-0 z-10 flex items-center justify-between bg-white border-b border-gray-200 px-4 py-3 sm:px-6 lg:px-8">
                <button onclick="toggleSidebar()" class="lg:hidden p-2 -ml-2 text-gray-500 hover:text-gray-700 rounded-lg hover:bg-gray-100 transition">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                </button>
                <div class="flex items-center gap-3">
                    @hasSection('breadcrumb')
                        @yield('breadcrumb')
                    @else
                        <h2 class="text-sm font-medium text-gray-500">@yield('page-title', 'Dashboard')</h2>
                    @endif
                </div>
                <div></div>
            </header>

            {{-- Page content --}}
            <main class="flex-1 p-4 sm:p-6 lg:p-8">
                @if(session('success'))
                    <div class="mb-6 flex items-center gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 flash">
                        <svg class="h-5 w-5 text-emerald-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                        {{ session('success') }}
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-6 flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 flash">
                        <svg class="h-5 w-5 text-red-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
                        {{ session('error') }}
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script src="/js/portal.js"></script>
    @php $version = file_exists(base_path('VERSION')) ? trim(file_get_contents(base_path('VERSION'))) : 'dev'; @endphp
    <div class="fixed bottom-2 right-3 text-xs text-gray-300 select-none pointer-events-none">v{{ $version }}</div>
</body>
</html>
