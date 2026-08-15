@extends('layouts.public')
@section('title', 'Portfolio - Guru Informatika')
@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <h1 class="text-3xl font-extrabold text-slate-900 mb-8 text-center">Project Portfolio</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
        <!-- Project 1 -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition">
            <div class="h-48 bg-gradient-to-r from-blue-400 to-indigo-500 flex items-center justify-center">
                <span class="text-white font-bold text-xl">Sistem Akademik Terpadu</span>
            </div>
            <div class="p-6">
                <h3 class="text-xl font-bold text-slate-900 mb-2">Web Presensi & Nilai</h3>
                <p class="text-slate-600 mb-4">Sistem manajemen sekolah yang mengintegrasikan absensi harian dan perhitungan nilai otomatis dengan desain premium.</p>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">Laravel</span>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">Tailwind</span>
            </div>
        </div>
    </div>
</div>
@endsection
