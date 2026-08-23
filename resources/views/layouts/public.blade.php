<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', ($app_settings['app_name'] ?? 'SPENSA') . ' | Portal Edukasi & Akademik')</title>
    <!-- Favicon -->
    @if(!empty($app_settings['app_logo']))
        @if(str_starts_with($app_settings['app_logo'], 'data:image'))
            <link rel="icon" type="image/png" href="{{ $app_settings['app_logo'] }}">
        @else
            <link rel="icon" type="image/png" href="{{ asset('uploads/logo/' . $app_settings['app_logo']) }}">
        @endif
    @else
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @endif
    <!-- Tailwind CSS (CDN) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brand: '#3b82f6',
                        dark: '#0f172a',
                    }
                }
            }
        }
    </script>
    <!-- Alpine.js (CDN) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700;800;900&display=swap');
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
        }
    </style>
</head>
<body class="text-slate-800 antialiased flex flex-col min-h-screen">

    <!-- Navigation -->
    <nav x-data="{ open: false }" class="bg-white/90 backdrop-blur-md fixed w-full z-50 border-b border-slate-200 shadow-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex-shrink-0 flex items-center">
                    <a href="{{ url('/') }}" class="flex items-center gap-3 group">
                        @if(!empty($app_settings['app_logo']))
                            <div class="w-9 h-9 rounded-xl overflow-hidden bg-white shadow-sm border border-slate-100 flex-shrink-0 flex items-center justify-center p-0.5">
                                @if(str_starts_with($app_settings['app_logo'], 'data:image'))
                                    <img src="{{ $app_settings['app_logo'] }}" class="w-full h-full object-contain" alt="Logo">
                                @else
                                    <img src="{{ asset('uploads/logo/' . $app_settings['app_logo']) }}" class="w-full h-full object-contain" alt="Logo" onerror="this.style.display='none'">
                                @endif
                            </div>
                        @else
                            <div class="w-9 h-9 bg-brand rounded-xl flex items-center justify-center text-white font-black text-lg shadow-md shadow-blue-500/20 flex-shrink-0">
                                {{ substr($app_settings['app_name'] ?? 'S', 0, 1) }}
                            </div>
                        @endif
                        <div class="flex flex-col">
                            <span class="text-lg font-extrabold tracking-tight text-slate-900 leading-tight group-hover:text-brand transition-colors">
                                {{ $app_settings['app_name'] ?? 'SPENSA' }}
                            </span>
                            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">
                                {{ $app_settings['app_subname'] ?? 'Informatika' }}
                            </span>
                        </div>
                    </a>
                </div>
                <!-- Desktop Menu -->
                <div class="hidden md:flex space-x-8 items-center">
                    <a href="{{ url('/login') }}" class="bg-brand text-white px-5 py-2.5 rounded-full font-bold text-sm hover:bg-blue-600 transition shadow-lg shadow-blue-500/30 active:scale-95">Masuk Sistem</a>
                </div>
                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button @click="open = !open" class="text-slate-600 hover:text-brand focus:outline-none p-2">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path x-show="!open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                            <path x-show="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
        <!-- Mobile Menu -->
        <div x-show="open" x-transition class="md:hidden bg-white border-b border-slate-200">
            <div class="px-4 pt-2 pb-4 space-y-2">
                <a href="{{ url('/login') }}" class="block px-4 py-2.5 text-center rounded-xl text-sm font-bold bg-brand text-white hover:bg-blue-600 shadow-md">Masuk Sistem</a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="flex-grow pt-16">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-8">
        <div class="max-w-7xl mx-auto px-4 text-center">
            <p class="text-slate-400 text-sm">
                &copy; {{ date('Y') }} <span class="text-white font-semibold">{{ $app_settings['app_name'] ?? 'SPENSA' }}</span>{{ !empty($app_settings['app_subname']) ? ' - ' . $app_settings['app_subname'] : '' }}. All rights reserved.
            </p>
        </div>
    </footer>
</body>
</html>
