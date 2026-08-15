@extends('layouts.public')
@section('title', 'Blog - Guru Informatika')
@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
    <h1 class="text-3xl font-extrabold text-slate-900 mb-8 text-center">Artikel Terbaru</h1>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-lg transition p-6">
            <span class="text-sm font-medium text-brand mb-2 block">Pendidikan</span>
            <h3 class="text-2xl font-bold text-slate-900 mb-2">Pentingnya Belajar Pemrograman Sejak Dini</h3>
            <p class="text-slate-600 mb-4">Pemrograman bukan sekadar menulis kode, melainkan cara melatih logika dan pemecahan masalah...</p>
            <a href="#" class="text-brand font-medium hover:underline">Baca selengkapnya &rarr;</a>
        </div>
    </div>
</div>
@endsection
