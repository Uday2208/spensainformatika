@extends('layouts.app')
@section('title', 'Dashboard Admin')
@section('page_title', 'Dashboard Admin')

@section('content')

{{-- ============================================================
     WELCOME BANNER (PANEL ADMINISTRATOR)
     ============================================================ --}}
<div class="relative overflow-hidden bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 rounded-2xl p-5 sm:p-6 mb-6 shadow-xl">
    <div class="absolute inset-0 opacity-10 pointer-events-none">
        <div class="absolute top-0 right-0 w-72 h-72 bg-white rounded-full -translate-y-32 translate-x-32"></div>
        <div class="absolute bottom-0 left-0 w-56 h-56 bg-amber-400 rounded-full translate-y-24 -translate-x-24"></div>
    </div>
    
    <div class="relative flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 sm:w-14 sm:h-14 bg-amber-400 rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
                <svg class="w-7 h-7 sm:w-8 sm:h-8 text-amber-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
            </div>
            <div class="min-w-0">
                <div class="inline-flex items-center gap-2 px-2.5 py-0.5 rounded-full bg-blue-700/60 border border-blue-400/30 text-blue-200 text-xs font-semibold mb-1.5 backdrop-blur-sm">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Panel Administrator
                </div>
                <h2 class="text-white font-bold text-xl sm:text-2xl leading-tight">Selamat Datang, {{ auth()->user()->name ?? 'Administrator' }}!</h2>
                <p class="text-blue-200 text-xs sm:text-sm mt-0.5">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2.5 w-full sm:w-auto">
            <a href="{{ url('/app/admin/data-guru') }}"
               class="flex-1 sm:flex-none inline-flex items-center justify-center gap-2 bg-amber-400 hover:bg-amber-300 text-amber-900 font-bold text-xs sm:text-sm px-4 py-2.5 rounded-xl shadow-lg transition-all active:scale-95 flex-shrink-0">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
                Kelola Guru
            </a>
            <a href="{{ url('/app/admin/pengaturan') }}"
               class="inline-flex items-center justify-center p-2.5 bg-white/10 hover:bg-white/20 text-white rounded-xl border border-white/15 transition-colors shadow-sm"
               title="Pengaturan Web">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                </svg>
            </a>
        </div>
    </div>
</div>

{{-- ============================================================
     8 STAT CARDS GRID
     ============================================================ --}}
<div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3.5 mb-6">
    
    {{-- Card 1: Total Guru --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Guru</p>
                <h3 class="text-2xl font-black text-slate-800 leading-tight">{{ $totalGuru }}</h3>
                <span class="text-[11px] text-blue-600 font-semibold">Pengajar aktif</span>
            </div>
        </div>
    </div>

    {{-- Card 2: Total Siswa --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Siswa</p>
                <h3 class="text-2xl font-black text-slate-800 leading-tight">{{ $totalSiswa }}</h3>
                <span class="text-[11px] text-indigo-600 font-semibold">Siswa terdaftar</span>
            </div>
        </div>
    </div>

    {{-- Card 3: Total Kelas --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Kelas</p>
                <h3 class="text-2xl font-black text-slate-800 leading-tight">{{ $totalKelas }}</h3>
                <span class="text-[11px] text-emerald-600 font-semibold">Rombel belajar</span>
            </div>
        </div>
    </div>

    {{-- Card 4: Hadir Hari Ini --}}
    <div class="bg-emerald-50 rounded-2xl border border-emerald-200 shadow-sm p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-emerald-500 text-white rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-bold text-emerald-700 uppercase tracking-wider">Hadir Hari Ini</p>
                <h3 class="text-2xl font-black text-emerald-800 leading-tight">{{ $hadirHariIni }}</h3>
                <span class="text-[11px] text-emerald-600 font-semibold">Siswa di kelas</span>
            </div>
        </div>
    </div>

    {{-- Card 5: Sakit / Izin --}}
    <div class="bg-amber-50 rounded-2xl border border-amber-200 shadow-sm p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-amber-500 text-white rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-bold text-amber-700 uppercase tracking-wider">Sakit / Izin</p>
                <h3 class="text-2xl font-black text-amber-800 leading-tight">{{ $sakitHariIni + $izinHariIni }}</h3>
                <span class="text-[11px] text-amber-600 font-semibold">S: {{ $sakitHariIni }} | I: {{ $izinHariIni }}</span>
            </div>
        </div>
    </div>

    {{-- Card 6: Alpha Hari Ini --}}
    <div class="bg-red-50 rounded-2xl border border-red-200 shadow-sm p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-red-500 text-white rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-bold text-red-700 uppercase tracking-wider">Alpha Hari Ini</p>
                <h3 class="text-2xl font-black text-red-800 leading-tight">{{ $alphaHariIni }}</h3>
                <span class="text-[11px] text-red-600 font-semibold">Tanpa keterangan</span>
            </div>
        </div>
    </div>

    {{-- Card 7: Status Presensi --}}
    <div class="{{ $sudahDiabsen ? 'bg-teal-50 border-teal-200' : 'bg-slate-50 border-slate-200' }} rounded-2xl border shadow-sm p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 {{ $sudahDiabsen ? 'bg-teal-500 text-white' : 'bg-slate-400 text-white' }} rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm">
                @if($sudahDiabsen)
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                @else
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                @endif
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-bold {{ $sudahDiabsen ? 'text-teal-700' : 'text-slate-500' }} uppercase tracking-wider">Status Presensi</p>
                <h3 class="text-2xl font-black {{ $sudahDiabsen ? 'text-teal-800' : 'text-slate-700' }} leading-tight">
                    {{ $sudahDiabsen ? 'Sudah' : 'Belum' }}
                </h3>
                <span class="text-[11px] {{ $sudahDiabsen ? 'text-teal-600' : 'text-slate-400' }} font-semibold">
                    {{ $sudahDiabsen ? 'Diabsen hari ini ✓' : 'Belum ada data' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Card 8: Ujian Aktif --}}
    <div class="bg-violet-50 rounded-2xl border border-violet-200 shadow-sm p-4 hover:shadow-md transition-shadow">
        <div class="flex items-center gap-3">
            <div class="w-11 h-11 bg-violet-600 text-white rounded-xl flex items-center justify-center flex-shrink-0 shadow-sm">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                </svg>
            </div>
            <div class="min-w-0">
                <p class="text-[11px] font-bold text-violet-700 uppercase tracking-wider">Ujian Aktif</p>
                <h3 class="text-2xl font-black text-violet-800 leading-tight">{{ $ujianAktif }}</h3>
                <span class="text-[11px] text-violet-600 font-semibold">Sedang berjalan</span>
            </div>
        </div>
    </div>

</div>

{{-- ============================================================
     MAIN DASHBOARD CONTENT: CHART & SIDE SECTIONS
     ============================================================ --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
    
    {{-- LEFT / MAIN COLUMN (2 cols): CHART & QUICK ACTIONS --}}
    <div class="lg:col-span-2 space-y-6">
        
        {{-- Chart.js Line Chart: Kehadiran 7 Hari Terakhir --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4 pb-3 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-600"></span>
                        Tren Kehadiran Siswa (7 Hari Terakhir)
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">Persentase rata-rata kehadiran harian siswa</p>
                </div>
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-blue-50 text-blue-700 text-xs font-semibold rounded-lg self-start sm:self-auto">
                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                    </svg>
                    Grafik Garis
                </div>
            </div>
            
            <div class="h-64 relative">
                <canvas id="chartKehadiran"></canvas>
            </div>
        </div>

        {{-- Quick Links to Admin Pages --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6">
            <h3 class="font-bold text-slate-800 text-base mb-4 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                Akses Cepat Menu Admin
            </h3>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                {{-- Quick Link: Data Guru --}}
                <a href="{{ url('/app/admin/data-guru') }}"
                   class="flex items-center gap-3.5 p-3.5 border border-slate-200 rounded-xl hover:bg-blue-50/70 hover:border-blue-300 transition-all group">
                    <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-blue-600 group-hover:text-white transition-colors shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h4 class="font-bold text-sm text-slate-800 group-hover:text-blue-700 transition-colors">Data Guru</h4>
                        <p class="text-xs text-slate-400 truncate">Kelola akun, tambah, & reset password</p>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-blue-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>

                {{-- Quick Link: Data Kelas --}}
                <a href="{{ url('/app/admin/data-kelas') }}"
                   class="flex items-center gap-3.5 p-3.5 border border-slate-200 rounded-xl hover:bg-emerald-50/70 hover:border-emerald-300 transition-all group">
                    <div class="w-10 h-10 bg-emerald-100 text-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-emerald-600 group-hover:text-white transition-colors shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h4 class="font-bold text-sm text-slate-800 group-hover:text-emerald-700 transition-colors">Data Kelas</h4>
                        <p class="text-xs text-slate-400 truncate">Atur rombongan belajar & tingkat</p>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-emerald-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>

                {{-- Quick Link: Data Siswa --}}
                <a href="{{ url('/app/admin/data-siswa') }}"
                   class="flex items-center gap-3.5 p-3.5 border border-slate-200 rounded-xl hover:bg-indigo-50/70 hover:border-indigo-300 transition-all group">
                    <div class="w-10 h-10 bg-indigo-100 text-indigo-600 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-indigo-600 group-hover:text-white transition-colors shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h4 class="font-bold text-sm text-slate-800 group-hover:text-indigo-700 transition-colors">Data Siswa</h4>
                        <p class="text-xs text-slate-400 truncate">Import CSV & manajemen akun siswa</p>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-indigo-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>

                {{-- Quick Link: Pengaturan --}}
                <a href="{{ url('/app/admin/pengaturan') }}"
                   class="flex items-center gap-3.5 p-3.5 border border-slate-200 rounded-xl hover:bg-violet-50/70 hover:border-violet-300 transition-all group">
                    <div class="w-10 h-10 bg-violet-100 text-violet-600 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-violet-600 group-hover:text-white transition-colors shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h4 class="font-bold text-sm text-slate-800 group-hover:text-violet-700 transition-colors">Pengaturan</h4>
                        <p class="text-xs text-slate-400 truncate">Nama sekolah, logo, KKM, & testimoni</p>
                    </div>
                    <svg class="w-4 h-4 text-slate-400 group-hover:text-violet-600 group-hover:translate-x-0.5 transition-all" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>

    </div>

    {{-- RIGHT COLUMN (1 col): 5 MOST RECENT GURU LIST --}}
    <div class="space-y-6">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sm:p-6 flex flex-col h-full">
            <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                <div>
                    <h3 class="font-bold text-slate-800 text-base flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Guru Terbaru
                    </h3>
                    <p class="text-xs text-slate-400 mt-0.5">5 akun guru terakhir ditambahkan</p>
                </div>
                <a href="{{ url('/app/admin/data-guru') }}"
                   class="text-xs font-bold text-blue-600 hover:text-blue-800 transition-colors">
                    Lihat Semua &rarr;
                </a>
            </div>

            <div class="flex-1 space-y-3">
                @forelse($guruList as $guru)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50 hover:bg-blue-50/60 border border-slate-100 transition-colors">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-600 to-indigo-700 text-white font-bold text-sm flex items-center justify-center flex-shrink-0 shadow-sm">
                                {{ strtoupper(substr($guru->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-bold text-xs sm:text-sm text-slate-800 truncate" title="{{ $guru->name }}">
                                    {{ $guru->name }}
                                </h4>
                                <p class="text-[11px] text-slate-400 truncate flex items-center gap-1 font-mono">
                                    <span>&#64;{{ $guru->username }}</span>
                                </p>
                            </div>
                        </div>
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700 flex-shrink-0 ml-2">
                            Guru
                        </span>
                    </div>
                @empty
                    <div class="py-8 text-center text-slate-400">
                        <div class="w-12 h-12 mx-auto mb-2 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
                            </svg>
                        </div>
                        <p class="text-xs font-semibold text-slate-500">Belum ada akun guru</p>
                        <p class="text-[11px] text-slate-400 mt-0.5">Tambahkan guru dari menu Data Guru.</p>
                        <a href="{{ url('/app/admin/data-guru') }}" class="inline-block mt-3 px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-lg transition-colors">
                            + Tambah Guru
                        </a>
                    </div>
                @endforelse
            </div>

            <div class="mt-4 pt-4 border-t border-slate-100">
                <a href="{{ url('/app/admin/data-guru') }}"
                   class="w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-700 font-bold text-xs rounded-xl border border-slate-200 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Tambah / Kelola Guru
                </a>
            </div>
        </div>
    </div>

</div>

{{-- ============================================================
     CHART.JS SCRIPT INITIALIZATION
     ============================================================ --}}
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const canvas = document.getElementById('chartKehadiran');
        if (!canvas) return;

        const ctx = canvas.getContext('2d');
        
        // Gradient fill di bawah garis
        const gradient = ctx.createLinearGradient(0, 0, 0, 220);
        gradient.addColorStop(0, 'rgba(37, 99, 235, 0.25)');
        gradient.addColorStop(1, 'rgba(37, 99, 235, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Kehadiran (%)',
                    data: @json($chartData),
                    borderColor: '#2563eb',
                    backgroundColor: gradient,
                    borderWidth: 3,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#ffffff',
                    pointBorderColor: '#2563eb',
                    pointBorderWidth: 2.5,
                    pointRadius: 4.5,
                    pointHoverRadius: 6.5,
                    pointHoverBackgroundColor: '#1d4ed8',
                    pointHoverBorderColor: '#ffffff',
                    pointHoverBorderWidth: 2.5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index',
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titleFont: { size: 12, weight: 'bold', family: 'Inter, sans-serif' },
                        bodyFont: { size: 12, family: 'Inter, sans-serif' },
                        padding: 10,
                        cornerRadius: 8,
                        displayColors: false,
                        callbacks: {
                            label: function(context) {
                                return `Kehadiran: ${context.parsed.y}%`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: {
                            stepSize: 20,
                            callback: function(value) {
                                return value + '%';
                            },
                            font: { size: 10, family: 'Inter, sans-serif' },
                            color: '#94a3b8'
                        },
                        grid: {
                            color: 'rgba(226, 232, 240, 0.6)',
                            drawBorder: false
                        }
                    },
                    x: {
                        ticks: {
                            font: { size: 10, family: 'Inter, sans-serif' },
                            color: '#64748b'
                        },
                        grid: {
                            display: false,
                            drawBorder: false
                        }
                    }
                }
            }
        });
    });
</script>

@endsection
