@extends('layouts.app')
@section('title', 'Dashboard Guru')
@section('page_title', 'Dashboard')
@section('content')

{{-- HEADER SELAMAT DATANG --}}
<div class="relative overflow-hidden bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 rounded-2xl p-5 mb-6 shadow-xl">
    <div class="absolute inset-0 opacity-10">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full -translate-y-32 translate-x-32"></div>
        <div class="absolute bottom-0 left-0 w-48 h-48 bg-amber-400 rounded-full translate-y-24 -translate-x-24"></div>
    </div>
    <div class="relative flex items-center gap-4">
        <div class="w-12 h-12 bg-amber-400 rounded-2xl flex items-center justify-center shadow-lg flex-shrink-0">
            <svg class="w-7 h-7 text-amber-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
        </div>
        <div class="flex-1 min-w-0">
            <h2 class="text-white font-bold text-xl leading-tight">Selamat Datang, {{ auth()->user()->name ?? 'Guru' }}!</h2>
            <p class="text-blue-200 text-sm mt-0.5">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</p>
        </div>
        <a href="{{ url('/app/absensi') }}"
           class="hidden sm:flex items-center gap-2 bg-amber-400 hover:bg-amber-300 text-amber-900 font-black text-sm px-5 py-2.5 rounded-xl shadow-lg transition-all active:scale-95 flex-shrink-0">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            Input Presensi
        </a>
    </div>
</div>

{{-- STAT CARDS (6 cards) --}}
<div class="grid grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-3 mb-6">
    {{-- Total Siswa --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
        </div>
        <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Total Siswa</p>
            <h3 class="text-2xl font-black text-slate-800">{{ $totalSiswa }}</h3>
        </div>
    </div>

    {{-- Total Kelas --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
        </div>
        <div>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-wide">Total Kelas</p>
            <h3 class="text-2xl font-black text-slate-800">{{ $totalKelas }}</h3>
        </div>
    </div>

    {{-- Hadir Hari Ini --}}
    <div class="bg-emerald-50 rounded-2xl border border-emerald-200 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-emerald-500 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
        </div>
        <div>
            <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-wide">Hadir Hari Ini</p>
            <h3 class="text-2xl font-black text-emerald-700">{{ $hadirHariIni }}</h3>
        </div>
    </div>

    {{-- Sakit Hari Ini --}}
    <div class="bg-amber-50 rounded-2xl border border-amber-200 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-amber-500 rounded-xl flex items-center justify-center flex-shrink-0">
            <span class="text-lg">🤒</span>
        </div>
        <div>
            <p class="text-[10px] font-bold text-amber-600 uppercase tracking-wide">Sakit/Izin</p>
            <h3 class="text-2xl font-black text-amber-700">{{ $sakitHariIni + $izinHariIni }}</h3>
        </div>
    </div>

    {{-- Alpha Hari Ini --}}
    <div class="bg-red-50 rounded-2xl border border-red-200 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-red-500 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path></svg>
        </div>
        <div>
            <p class="text-[10px] font-bold text-red-600 uppercase tracking-wide">Alpha Hari Ini</p>
            <h3 class="text-2xl font-black text-red-700">{{ $alphaHariIni }}</h3>
        </div>
    </div>

    {{-- Komentar --}}
    <div class="bg-violet-50 rounded-2xl border border-violet-200 shadow-sm p-4 flex items-center gap-3">
        <div class="w-10 h-10 bg-violet-500 rounded-xl flex items-center justify-center flex-shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
        </div>
        <div>
            <p class="text-[10px] font-bold text-violet-600 uppercase tracking-wide">Komentar</p>
            <h3 class="text-2xl font-black text-violet-700">{{ $totalKomentar }}</h3>
        </div>
    </div>
</div>

{{-- BANNER ABSENSI HARI INI --}}
@if($sudahDiabsen == 0)
<div class="flex items-center gap-3 bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-6">
    <div class="w-9 h-9 bg-amber-400 rounded-xl flex items-center justify-center flex-shrink-0">
        <svg class="w-5 h-5 text-amber-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
    </div>
    <div class="flex-1">
        <p class="font-bold text-amber-800 text-sm">Belum ada absensi hari ini!</p>
        <p class="text-amber-700 text-xs">Gunakan menu Input Presensi untuk merekam kehadiran siswa sekarang.</p>
    </div>
    <a href="{{ url('/app/absensi') }}" class="flex-shrink-0 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs rounded-xl transition-colors">
        Input Sekarang
    </a>
</div>
@else
<div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 rounded-2xl p-4 mb-6">
    <div class="w-9 h-9 bg-emerald-500 rounded-xl flex items-center justify-center flex-shrink-0">
        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
    </div>
    <div class="flex-1">
        <p class="font-bold text-emerald-800 text-sm">Absensi hari ini sudah direkam ✓</p>
        <p class="text-emerald-700 text-xs">{{ $sudahDiabsen }} data tercatat — Hadir: {{ $hadirHariIni }}, Sakit: {{ $sakitHariIni }}, Izin: {{ $izinHariIni }}, Alpha: {{ $alphaHariIni }}</p>
    </div>
    <a href="{{ url('/app/absensi') }}" class="flex-shrink-0 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl transition-colors">
        Edit / Tambah
    </a>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
    {{-- CHART KEHADIRAN 7 HARI --}}
    <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
        <h3 class="font-bold text-slate-700 text-sm mb-4">📊 Grafik Kehadiran 7 Hari Terakhir</h3>
        <div class="h-56">
            <canvas id="absensiChart"></canvas>
        </div>
    </div>

    {{-- QUICK ACCESS --}}
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
        <h3 class="font-bold text-slate-700 text-sm mb-4">⚡ Akses Cepat</h3>
        <div class="grid grid-cols-2 gap-2">
            {{-- Input Presensi (unggulan) --}}
            <a href="{{ url('/app/absensi') }}"
               class="col-span-2 flex items-center gap-3 p-3 bg-gradient-to-r from-amber-50 to-orange-50 border-2 border-amber-300 rounded-xl hover:border-amber-400 hover:shadow-md transition-all group">
                <div class="w-9 h-9 bg-amber-400 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:bg-amber-500 transition-colors">
                    <svg class="w-5 h-5 text-amber-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                </div>
                <div>
                    <h4 class="font-black text-sm text-amber-900">Input Presensi</h4>
                    <p class="text-[10px] text-amber-700">Catat kehadiran siswa hari ini</p>
                </div>
            </a>

            <a href="{{ url('/app/data-kelas') }}" class="flex flex-col p-3 border border-slate-200 rounded-xl hover:bg-blue-50 hover:border-blue-300 transition-colors group">
                <div class="w-7 h-7 bg-blue-100 rounded-lg flex items-center justify-center mb-2 group-hover:bg-blue-500 transition-colors">
                    <svg class="w-4 h-4 text-blue-600 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <h4 class="font-bold text-xs text-slate-800">Kelola Kelas</h4>
                <p class="text-[10px] text-slate-400 mt-0.5">Atur data kelas</p>
            </a>

            <a href="{{ url('/app/data-siswa') }}" class="flex flex-col p-3 border border-slate-200 rounded-xl hover:bg-indigo-50 hover:border-indigo-300 transition-colors group">
                <div class="w-7 h-7 bg-indigo-100 rounded-lg flex items-center justify-center mb-2 group-hover:bg-indigo-500 transition-colors">
                    <svg class="w-4 h-4 text-indigo-600 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <h4 class="font-bold text-xs text-slate-800">Data Siswa</h4>
                <p class="text-[10px] text-slate-400 mt-0.5">Import/Export CSV</p>
            </a>

            <a href="{{ url('/app/nilai') }}" class="flex flex-col p-3 border border-slate-200 rounded-xl hover:bg-orange-50 hover:border-orange-300 transition-colors group">
                <div class="w-7 h-7 bg-orange-100 rounded-lg flex items-center justify-center mb-2 group-hover:bg-orange-500 transition-colors">
                    <svg class="w-4 h-4 text-orange-600 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <h4 class="font-bold text-xs text-slate-800">Input Nilai</h4>
                <p class="text-[10px] text-slate-400 mt-0.5">Tugas, Quiz, Proyek</p>
            </a>

            <a href="{{ url('/app/rekap-absensi') }}" class="flex flex-col p-3 border border-slate-200 rounded-xl hover:bg-teal-50 hover:border-teal-300 transition-colors group">
                <div class="w-7 h-7 bg-teal-100 rounded-lg flex items-center justify-center mb-2 group-hover:bg-teal-500 transition-colors">
                    <svg class="w-4 h-4 text-teal-600 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <h4 class="font-bold text-xs text-slate-800">Rekap Absensi</h4>
                <p class="text-[10px] text-slate-400 mt-0.5">Laporan + Export CSV</p>
            </a>

            <a href="{{ url('/app/kelola-komentar') }}" class="flex flex-col p-3 border border-slate-200 rounded-xl hover:bg-pink-50 hover:border-pink-300 transition-colors group">
                <div class="w-7 h-7 bg-pink-100 rounded-lg flex items-center justify-center mb-2 group-hover:bg-pink-500 transition-colors">
                    <svg class="w-4 h-4 text-pink-600 group-hover:text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                </div>
                <h4 class="font-bold text-xs text-slate-800">Komentar</h4>
                <p class="text-[10px] text-slate-400 mt-0.5 inline-flex items-center gap-1">
                    Kelola testimoni
                    @if($totalKomentar > 0)
                    <span class="bg-violet-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full">{{ $totalKomentar }}</span>
                    @endif
                </p>
            </a>
        </div>
    </div>
</div>

<script>
    document.addEventListener("DOMContentLoaded", function() {
        const ctx = document.getElementById('absensiChart').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: @json($chartLabels),
                datasets: [{
                    label: 'Kehadiran (%)',
                    data: @json($chartData),
                    backgroundColor: function(context) {
                        const value = context.dataset.data[context.dataIndex];
                        if (value >= 90) return 'rgba(16, 185, 129, 0.85)';
                        if (value >= 75) return 'rgba(59, 130, 246, 0.85)';
                        if (value >= 50) return 'rgba(245, 158, 11, 0.85)';
                        return value === 0 ? 'rgba(203, 213, 225, 0.6)' : 'rgba(239, 68, 68, 0.85)';
                    },
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.parsed.y === 0 ? 'Tidak ada data' : `Kehadiran: ${ctx.parsed.y}%`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        ticks: { callback: v => v + '%', font: { size: 10 } },
                        grid: { color: 'rgba(0,0,0,0.04)' }
                    },
                    x: {
                        ticks: { font: { size: 10 } },
                        grid: { display: false }
                    }
                }
            }
        });
    });
</script>

@endsection
