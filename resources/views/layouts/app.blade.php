<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $app_settings['app_name'] ?? 'Sistem Akademik' }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        /* Custom compact styling */
        body { background-color: #f8fafc; color: #334155; font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }
        
        /* Custom slim scrollbar for sidebar */
        .sidebar-scroll::-webkit-scrollbar { width: 5px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.2); border-radius: 4px; }
        .sidebar-scroll::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.4); }
        .sidebar-scroll { scrollbar-width: thin; scrollbar-color: rgba(255, 255, 255, 0.2) transparent; }
    </style>
</head>
<body class="antialiased bg-slate-50 flex h-screen overflow-hidden" x-data="{ sidebarOpen: false, avatarModalOpen: false }">

    <!-- Overlay -->
    <div x-cloak x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false" 
         class="fixed inset-0 bg-black/50 z-40 lg:hidden backdrop-blur-sm"></div>

    <!-- Sidebar -->
    <div :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
         class="fixed inset-y-0 left-0 z-50 w-[270px] flex flex-col h-full bg-gradient-to-b from-blue-900 to-blue-800 text-white transform transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-auto lg:flex lg:h-screen lg:w-[260px] lg:flex-col shadow-2xl">
        
        <!-- Sidebar Header -->
        <div class="flex items-center justify-between h-[60px] px-6 bg-blue-950/30 border-b border-blue-800/50">
            <a href="{{ url('/') }}" class="flex items-center gap-3 hover:opacity-90 transition-opacity" title="Menuju Halaman Utama">
                @if(!empty($app_settings['app_logo']))
                    <div class="w-8 h-8 rounded-lg overflow-hidden bg-white shadow-inner flex-shrink-0 flex items-center justify-center p-0.5">
                        @if(str_starts_with($app_settings['app_logo'], 'data:image'))
                            <img src="{{ $app_settings['app_logo'] }}" class="w-full h-full object-contain" alt="Logo">
                        @else
                            <img src="{{ asset('uploads/logo/' . $app_settings['app_logo']) }}" class="w-full h-full object-contain" alt="Logo" onerror="this.style.display='none'">
                        @endif
                    </div>
                @else
                    <div class="w-8 h-8 bg-white rounded-lg flex items-center justify-center text-blue-900 font-bold text-xl shadow-inner flex-shrink-0">
                        {{ substr($app_settings['app_name'] ?? 'S', 0, 1) }}
                    </div>
                @endif
                <div>
                    <h2 class="text-sm font-bold tracking-wider leading-tight">{{ $app_settings['app_name'] ?? 'SPENSA' }}</h2>
                    <p class="text-[10px] text-blue-200 uppercase tracking-widest">{{ $app_settings['app_subname'] ?? 'Presensi Digital' }}</p>
                </div>
            </a>
            <!-- Close button for mobile -->
            <button @click="sidebarOpen = false" class="lg:hidden p-2 rounded-md text-blue-200 hover:text-white hover:bg-white/10 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <!-- Sidebar Navigation -->
        <nav class="flex-1 overflow-y-auto overscroll-contain touch-pan-y py-4 px-3 space-y-6 sidebar-scroll pb-24">
            
            @if(auth()->user()->role == 'guru')
            <!-- Menu Utama -->
            <div>
                <p class="px-3 text-xs font-semibold text-blue-300 uppercase tracking-wider mb-2">Menu Utama</p>
                <div class="space-y-1">
                    <a href="{{ url('/app/dashboard-guru') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium {{ request()->is('app/dashboard-guru') ? 'bg-blue-950/50 text-white relative before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1.5 before:bg-blue-400 before:rounded-r-full shadow-inner' : 'text-blue-100 hover:bg-white/10 hover:text-white' }} transition-all min-h-[44px]">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                        Dashboard
                    </a>
                </div>
            </div>

            <!-- Master Data -->
            <div>
                <p class="px-3 text-xs font-semibold text-blue-300 uppercase tracking-wider mb-2">Master Data</p>
                <div class="space-y-1">
                    <a href="{{ url('/app/data-kelas') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium {{ request()->is('app/data-kelas') ? 'bg-blue-950/50 text-white relative before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1.5 before:bg-blue-400 before:rounded-r-full shadow-inner' : 'text-blue-100 hover:bg-white/10 hover:text-white' }} transition-all min-h-[44px]">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        Data Kelas
                    </a>
                    <a href="{{ url('/app/data-siswa') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium {{ request()->is('app/data-siswa') ? 'bg-blue-950/50 text-white relative before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1.5 before:bg-blue-400 before:rounded-r-full shadow-inner' : 'text-blue-100 hover:bg-white/10 hover:text-white' }} transition-all min-h-[44px]">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        Data Siswa
                    </a>
                </div>
            </div>

            <!-- Presensi & Akademik -->
            <div>
                <p class="px-3 text-xs font-semibold text-blue-300 uppercase tracking-wider mb-2">Presensi & Akademik</p>
                <div class="space-y-1">
                    <a href="{{ url('/app/absensi') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium {{ request()->is('app/absensi') ? 'bg-blue-950/50 text-white relative before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1.5 before:bg-blue-400 before:rounded-r-full shadow-inner' : 'text-blue-100 hover:bg-white/10 hover:text-white' }} transition-all min-h-[44px]">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        Input Presensi
                    </a>
                    <a href="{{ url('/app/penilaian-harian') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium {{ request()->is('app/penilaian-harian') ? 'bg-blue-950/50 text-white relative before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1.5 before:bg-blue-400 before:rounded-r-full shadow-inner' : 'text-blue-100 hover:bg-white/10 hover:text-white' }} transition-all min-h-[44px]">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Input Nilai Keaktifan
                    </a>
                    <a href="{{ url('/app/nilai') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium {{ request()->is('app/nilai') ? 'bg-blue-950/50 text-white relative before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1.5 before:bg-blue-400 before:rounded-r-full shadow-inner' : 'text-blue-100 hover:bg-white/10 hover:text-white' }} transition-all min-h-[44px]">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Input Nilai Akhir
                    </a>
                    <a href="{{ url('/app/ujian') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium {{ request()->is('app/ujian') || request()->is('app/ujian/*') ? 'bg-blue-950/50 text-white relative before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1.5 before:bg-blue-400 before:rounded-r-full shadow-inner' : 'text-blue-100 hover:bg-white/10 hover:text-white' }} transition-all min-h-[44px]">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        Ujian Harian
                    </a>
                    <a href="{{ url('/app/hasil-ujian') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium {{ request()->is('app/hasil-ujian*') ? 'bg-blue-950/50 text-white relative before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1.5 before:bg-blue-400 before:rounded-r-full shadow-inner' : 'text-blue-100 hover:bg-white/10 hover:text-white' }} transition-all min-h-[44px]">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Hasil & Koreksi Ujian
                    </a>
                </div>
            </div>

            <!-- Konten -->
            <div>
                <p class="px-3 text-xs font-semibold text-blue-300 uppercase tracking-wider mb-2">Konten</p>
                <div class="space-y-1">
                    <a href="{{ url('/app/materi') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium {{ request()->is('app/materi') ? 'bg-blue-950/50 text-white relative before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1.5 before:bg-blue-400 before:rounded-r-full shadow-inner' : 'text-blue-100 hover:bg-white/10 hover:text-white' }} transition-all min-h-[44px]">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        Materi Belajar
                    </a>
                    <a href="{{ url('/app/artikel') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium {{ request()->is('app/artikel') ? 'bg-blue-950/50 text-white relative before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1.5 before:bg-blue-400 before:rounded-r-full shadow-inner' : 'text-blue-100 hover:bg-white/10 hover:text-white' }} transition-all min-h-[44px]">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
                        Artikel Web
                    </a>
                </div>
            </div>

            <!-- Laporan -->
            <div>
                <p class="px-3 text-xs font-semibold text-blue-300 uppercase tracking-wider mb-2">Laporan & Pengaturan</p>
                <div class="space-y-1">
                    <!-- Dropdown Laporan -->
                    <div x-data="{ openLaporan: {{ request()->is('app/rekap*') ? 'true' : 'false' }} }" class="space-y-1">
                        <button @click="openLaporan = !openLaporan" class="w-full flex items-center justify-between px-3 py-3 rounded-xl text-sm font-medium {{ request()->is('app/rekap*') ? 'bg-blue-950/50 text-white relative before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1.5 before:bg-blue-400 before:rounded-r-full shadow-inner' : 'text-blue-100 hover:bg-white/10 hover:text-white' }} transition-all min-h-[44px]">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Laporan
                            </div>
                            <svg :class="openLaporan ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                        </button>
                        
                        <!-- Sub menu Laporan -->
                        <div x-show="openLaporan" x-collapse class="pl-11 pr-3 py-1 space-y-1">
                            <a href="{{ url('/app/rekap-absensi') }}" class="block px-3 py-2 rounded-lg text-sm {{ request()->is('app/rekap-absensi*') ? 'text-white bg-white/10 font-semibold' : 'text-blue-200 hover:text-white hover:bg-white/5' }} transition-colors">
                                Rekap Presensi
                            </a>
                            <a href="{{ url('/app/rekap-keaktifan') }}" class="block px-3 py-2 rounded-lg text-sm {{ request()->is('app/rekap-keaktifan*') ? 'text-white bg-white/10 font-semibold' : 'text-blue-200 hover:text-white hover:bg-white/5' }} transition-colors">
                                Rekap Nilai Keaktifan
                            </a>
                            <a href="{{ url('/app/rekap-jurnal') }}" class="block px-3 py-2 rounded-lg text-sm {{ request()->is('app/rekap-jurnal*') ? 'text-white bg-white/10 font-semibold' : 'text-blue-200 hover:text-white hover:bg-white/5' }} transition-colors">
                                Rekap Jurnal Mengajar
                            </a>
                        </div>
                    </div>
                    <a href="{{ url('/app/pengaturan') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium {{ request()->is('app/pengaturan') ? 'bg-blue-950/50 text-white relative before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1.5 before:bg-blue-400 before:rounded-r-full shadow-inner' : 'text-blue-100 hover:bg-white/10 hover:text-white' }} transition-all min-h-[44px]">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        Pengaturan Web
                    </a>
                </div>
            </div>

            @else
            <!-- Menu Siswa -->
            <div>
                <p class="px-3 text-xs font-semibold text-blue-300 uppercase tracking-wider mb-2">Menu Siswa</p>
                <div class="space-y-1">
                    <a href="{{ url('/app/dashboard-siswa') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium {{ request()->is('app/dashboard-siswa') ? 'bg-blue-950/50 text-white relative before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1.5 before:bg-blue-400 before:rounded-r-full shadow-inner' : 'text-blue-100 hover:bg-white/10 hover:text-white' }} transition-all min-h-[44px]">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                        Dashboard
                    </a>
                    <a href="{{ url('/app/kehadiran-saya') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium {{ request()->is('app/kehadiran-saya*') ? 'bg-blue-950/50 text-white relative before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1.5 before:bg-blue-400 before:rounded-r-full shadow-inner' : 'text-blue-100 hover:bg-white/10 hover:text-white' }} transition-all min-h-[44px]">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        Kehadiran Saya
                    </a>
                    <a href="{{ url('/app/ujian-siswa') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium {{ request()->is('app/ujian-siswa*') ? 'bg-blue-950/50 text-white relative before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1.5 before:bg-blue-400 before:rounded-r-full shadow-inner' : 'text-blue-100 hover:bg-white/10 hover:text-white' }} transition-all min-h-[44px]">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                        Ujian Harian
                    </a>
                    <a href="{{ url('/app/materi-siswa') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium {{ request()->is('app/materi-siswa*') ? 'bg-blue-950/50 text-white relative before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1.5 before:bg-blue-400 before:rounded-r-full shadow-inner' : 'text-blue-100 hover:bg-white/10 hover:text-white' }} transition-all min-h-[44px]">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        Materi Belajar
                    </a>
                    <a href="{{ url('/app/kesan-masukan') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium {{ request()->is('app/kesan-masukan*') ? 'bg-blue-950/50 text-white relative before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1.5 before:bg-blue-400 before:rounded-r-full shadow-inner' : 'text-blue-100 hover:bg-white/10 hover:text-white' }} transition-all min-h-[44px]">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                        Kesan & Masukan
                    </a>
                    <a href="{{ url('/app/profil-saya') }}" class="flex items-center gap-3 px-3 py-3 rounded-xl text-sm font-medium {{ request()->is('app/profil-saya*') ? 'bg-blue-950/50 text-white relative before:absolute before:left-0 before:top-2 before:bottom-2 before:w-1.5 before:bg-blue-400 before:rounded-r-full shadow-inner' : 'text-blue-100 hover:bg-white/10 hover:text-white' }} transition-all min-h-[44px]">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                        Akun & Profil
                    </a>
                </div>
            </div>
            @endif

        </nav>
    </div>

    <!-- Main Content Wrapper -->
    <div class="flex-1 flex flex-col h-screen overflow-hidden relative w-full lg:w-auto transition-all duration-300">
        
        <!-- Header Atas Navbar -->
        <header class="h-[60px] bg-white border-b border-slate-200 flex items-center justify-between px-4 sm:px-6 flex-shrink-0 shadow-sm z-30 relative">
            <div class="flex items-center gap-4">
                <button @click="sidebarOpen = true" class="p-2 -ml-2 rounded-xl text-slate-500 hover:bg-slate-100 lg:hidden focus:outline-none focus:ring-2 focus:ring-blue-500 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                </button>
                <h1 class="text-lg font-bold text-slate-800 tracking-tight truncate">@yield('page_title', 'Dashboard')</h1>
            </div>
            
            <div class="flex items-center gap-3">
                <div class="hidden sm:block text-xs font-medium text-slate-500 bg-slate-100 px-3 py-1.5 rounded-full border border-slate-200">
                    {{ \Carbon\Carbon::now()->translatedFormat('d M Y') }}
                </div>
                
                <!-- Dropdown Profile User di Navbar (Pojok Kanan Atas) -->
                <div x-data="{ userMenuOpen: false }" class="relative">
                    <button @click="userMenuOpen = !userMenuOpen" @click.outside="userMenuOpen = false" class="flex items-center gap-2 p-1 rounded-full hover:bg-slate-100 transition-colors focus:outline-none focus:ring-2 focus:ring-blue-500/20">
                        <img src="{{ Auth::user()->avatar_url }}" class="w-9 h-9 rounded-full object-cover border-2 border-blue-500 shadow-sm" alt="{{ Auth::user()->name }}">
                        <span class="hidden md:inline-block font-semibold text-slate-700 text-sm">{{ Auth::user()->name }}</span>
                        <svg class="w-4 h-4 text-slate-500 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-cloak x-show="userMenuOpen" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="transform opacity-0 scale-95"
                         x-transition:enter-end="transform opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         x-transition:leave-start="transform opacity-100 scale-100"
                         x-transition:leave-end="transform opacity-0 scale-95"
                         class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-xl border border-slate-100 py-2 z-50">
                        
                        <!-- Profile Header -->
                        <div class="px-4 py-3 border-b border-slate-100 bg-slate-50/50">
                            <p class="text-sm font-bold text-slate-800 truncate">{{ Auth::user()->name }}</p>
                            <span class="inline-block mt-1 px-2 py-0.5 text-[10px] font-semibold text-blue-700 bg-blue-50 rounded-full capitalize">
                                {{ Auth::user()->role ?? 'User' }}
                            </span>
                        </div>

                        <!-- Action Items -->
                        <div class="py-1">
                            @if(Auth::user()->role === 'siswa')
                            <a href="{{ url('/app/profil-saya') }}" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors text-left">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Pengaturan Akun
                            </a>
                            @endif

                            <button @click="avatarModalOpen = true; userMenuOpen = false" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 transition-colors text-left">
                                <svg class="w-4 h-4 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Ganti Foto Profil
                            </button>

                            <a href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="w-full flex items-center gap-2 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                                </svg>
                                Logout
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Form Hidden Logout -->
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">
                    @csrf
                </form>
            </div>
        </header>

        <!-- Main Scrollable Content -->
        <main class="flex-1 overflow-x-hidden overflow-y-auto overscroll-contain touch-pan-y bg-slate-50 p-4 sm:p-6 lg:p-8 scroll-smooth">
            <div class="max-w-7xl mx-auto pb-16">
                @yield('content')
            </div>
        </main>
    </div>
    </div>

    <!-- Avatar Upload Modal -->
    <div x-cloak x-show="avatarModalOpen" class="fixed inset-0 z-[100] flex items-center justify-center overflow-y-auto overflow-x-hidden p-4">
        <!-- Backdrop -->
        <div x-show="avatarModalOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="avatarModalOpen = false" 
             class="fixed inset-0 bg-black/60 backdrop-blur-sm transition-opacity"></div>
        
        <!-- Modal Content -->
        <div x-show="avatarModalOpen" 
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 overflow-hidden transform transition-all text-center">
             
            <!-- Close Button -->
            <button @click="avatarModalOpen = false" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600 transition-colors bg-slate-100 rounded-full p-1">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>

            <h3 class="text-lg font-bold text-slate-800 mb-1">Ganti Foto Profil</h3>
            <p class="text-xs text-slate-500 mb-6">Personalisasikan akun Anda (Max 2MB)</p>

            <form id="avatarUploadForm" action="{{ url('/app/avatar') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                
                <div class="relative w-32 h-32 mx-auto group">
                    <div class="w-full h-full rounded-full border-4 border-slate-100 shadow-md overflow-hidden bg-slate-100 text-slate-300 flex items-center justify-center bg-cover bg-center" style="background-image: url('{{ auth()->user()->avatar_url }}')">
                    </div>
                    
                    <label class="absolute inset-0 w-full h-full bg-black/50 rounded-full flex flex-col items-center justify-center text-white opacity-0 group-hover:opacity-100 cursor-pointer transition-opacity backdrop-blur-sm">
                        <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                        <span class="text-xs font-bold" id="avatarUploadText">Pilih Foto</span>
                        <input type="file" id="avatarFileInput" name="avatar" class="hidden" accept=".jpg,.jpeg,.png,.webp" required onchange="handleAvatarUpload(this)">
                    </label>
                </div>

                <p class="text-[10px] text-slate-400">Pilih file foto dari perangkat Anda untuk langsung mengganti foto profil.</p>
            </form>
        </div>
    </div>

    <script>
    function handleAvatarUpload(input) {
        if (!input.files || !input.files[0]) return;
        const file = input.files[0];
        const textLabel = document.getElementById('avatarUploadText');

        if (!file.type.match(/^image\/(jpeg|png|jpg|webp)$/)) {
            alert('Format file tidak didukung. Gunakan format JPG, PNG, atau WebP.');
            input.value = '';
            return;
        }

        if (textLabel) textLabel.innerText = 'Mengompres...';

        const sendCompressedBlob = (blob) => {
            if (textLabel) textLabel.innerText = 'Menyimpan...';

            const formData = new FormData();
            formData.append('_token', '{{ csrf_token() }}');
            formData.append('avatar', blob, 'avatar.jpg');

            fetch('{{ url("/app/avatar") }}', {
                method: 'POST',
                headers: { 
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    if (textLabel) textLabel.innerText = 'Berhasil!';
                    setTimeout(() => {
                        window.location.reload();
                    }, 500);
                } else {
                    alert(data.message || 'Gagal menyimpan foto profil.');
                    if (textLabel) textLabel.innerText = 'Pilih Foto';
                    input.value = '';
                }
            })
            .catch(err => {
                // Fallback reload jika respon redirect
                window.location.reload();
            });
        };

        // Micro-Square Compression: 150x150px (hanya ~10KB - 15KB)
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = new Image();
            img.onload = function() {
                try {
                    const canvas = document.createElement('canvas');
                    const targetSize = 150;
                    canvas.width = targetSize;
                    canvas.height = targetSize;
                    const ctx = canvas.getContext('2d');

                    // Center Crop square
                    let srcX = 0, srcY = 0, srcSize = Math.min(img.width, img.height);
                    if (img.width > img.height) {
                        srcX = (img.width - img.height) / 2;
                    } else {
                        srcY = (img.height - img.width) / 2;
                    }

                    ctx.drawImage(img, srcX, srcY, srcSize, srcSize, 0, 0, targetSize, targetSize);

                    canvas.toBlob(function(blob) {
                        if (blob && blob.size > 0) {
                            sendCompressedBlob(blob);
                        } else {
                            alert('Gagal memproses gambar. Silakan coba lagi.');
                            if (textLabel) textLabel.innerText = 'Pilih Foto';
                            input.value = '';
                        }
                    }, 'image/jpeg', 0.85);
                } catch (err) {
                    alert('Gagal memproses gambar di browser.');
                    if (textLabel) textLabel.innerText = 'Pilih Foto';
                    input.value = '';
                }
            };
            img.onerror = function() {
                alert('File yang dipilih bukan gambar yang valid.');
                if (textLabel) textLabel.innerText = 'Pilih Foto';
                input.value = '';
            };
            img.src = e.target.result;
        };
        reader.onerror = function() {
            alert('Gagal membaca file dari perangkat.');
            if (textLabel) textLabel.innerText = 'Pilih Foto';
            input.value = '';
        };
        reader.readAsDataURL(file);
    }
    </script>
</body>
</html>
