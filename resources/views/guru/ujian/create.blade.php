@extends('layouts.app')
@section('title', 'Buat Ujian Baru')
@section('page_title', 'Buat Ujian Baru')
@section('content')

@if($errors->any())
<div class="mb-4 bg-red-100 text-red-800 p-3 rounded-xl text-sm border border-red-200 shadow-sm">
    @foreach ($errors->all() as $error)
        <p>{{ $error }}</p>
    @endforeach
</div>
@endif

<div class="max-w-xl bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
    <div class="mb-5 pb-4 border-b border-slate-100">
        <h3 class="text-base font-extrabold text-slate-800">Langkah 1: Informasi Ujian</h3>
        <p class="text-xs text-slate-500 mt-1">Isi judul dan materi ujian terlebih dahulu. Waktu, durasi, token, dan kelas target diatur saat ujian akan diaktifkan.</p>
    </div>

    <form action="{{ route('guru.ujian.store') }}" method="POST">
        @csrf
        
        <div class="mb-4">
            <label class="block text-sm font-semibold text-slate-700 mb-1">Judul Ujian</label>
            <input type="text" name="judul" required placeholder="Contoh: Ulangan Harian 1 - Aljabar" value="{{ old('judul') }}" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
        </div>

        <div class="mb-5">
            <label class="block text-sm font-semibold text-slate-700 mb-1">Materi / Bab</label>
            <input type="text" name="bab" required list="daftarBabList" placeholder="Contoh: Bab 1 - Aljabar" value="{{ old('bab') }}" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:bg-white transition-all">
            <datalist id="daftarBabList">
                @foreach($daftar_bab as $bab)
                    <option value="{{ $bab }}">
                @endforeach
            </datalist>
            <p class="text-xs text-slate-400 mt-1.5">* Pastikan nama Bab sesuai dengan Bab di Rekap Nilai agar nilai ulangan otomatis tersinkronisasi.</p>
        </div>

        <div class="bg-blue-50/50 border border-blue-200 rounded-xl p-3.5 mb-6 text-xs text-blue-800 flex items-start gap-2.5">
            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            <div>
                <span class="font-bold">Info:</span> Setelah membuat ujian, Anda dapat menambahkan soal-soal. Tanggal, durasi, token, dan aktivasi pilihan kelas akan diatur melalui menu <strong>Setting Ujian</strong>.
            </div>
        </div>

        <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
            <a href="{{ route('guru.ujian.index') }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-xl text-sm font-semibold transition-all">Batal</a>
            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-xl text-sm font-extrabold shadow-md transition-all active:scale-[0.98]">
                Buat Ujian & Lanjut Tambah Soal ➔
            </button>
        </div>
    </form>
</div>

@endsection
