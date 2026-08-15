@extends('layouts.app')
@section('title', 'Hasil Ujian')
@section('page_title', 'Hasil Ujian')
@section('content')

@if(session('success'))
<div class="mb-4 bg-green-100 text-green-800 p-3 rounded text-sm border border-green-200 shadow-sm">
    {{ session('success') }}
</div>
@endif

<div class="max-w-md mx-auto bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden p-6 text-center">
    
    <!-- Icon/Illustration -->
    <div class="w-20 h-20 bg-blue-50 border border-blue-100 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
        <svg class="w-10 h-10 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    </div>

    <h2 class="text-xl font-black text-slate-800 mb-1">Ujian Selesai Dikirim!</h2>
    <p class="text-xs text-slate-500 mb-5">Terima kasih telah menyelesaikan ujian ini dengan jujur.</p>

    <!-- Details Card -->
    <div class="bg-slate-50 rounded-xl border border-slate-200 p-4 text-left mb-6 space-y-2.5">
        <div class="flex justify-between text-xs">
            <span class="text-slate-400 font-semibold">Nama Ujian:</span>
            <span class="font-extrabold text-slate-700 text-right max-w-[200px] truncate">{{ $ujian->judul }}</span>
        </div>
        <div class="flex justify-between text-xs">
            <span class="text-slate-400 font-semibold">Materi / Bab:</span>
            <span class="font-bold text-slate-700">{{ $ujian->bab }}</span>
        </div>
        <div class="flex justify-between text-xs">
            <span class="text-slate-400 font-semibold">Tanggal Pelaksanaan:</span>
            <span class="font-medium text-slate-600">{{ \Carbon\Carbon::parse($ujian->tanggal)->translatedFormat('d F Y') }}</span>
        </div>
        <div class="flex justify-between text-xs">
            <span class="text-slate-400 font-semibold">Waktu Pengumpulan:</span>
            <span class="font-mono text-slate-600">{{ $hasil->finished_at ? \Carbon\Carbon::parse($hasil->finished_at)->translatedFormat('d M H:i') : '-' }}</span>
        </div>
        <div class="flex justify-between text-xs items-center pt-2.5 border-t border-slate-200">
            <span class="text-slate-400 font-semibold">Status Nilai:</span>
            <span>
                @if($hasil->status === 'selesai')
                <span class="px-2 py-0.5 bg-amber-100 text-amber-800 text-[10px] font-extrabold rounded-lg border border-amber-200 uppercase">Proses Koreksi</span>
                @else
                <span class="px-2 py-0.5 bg-green-100 text-green-800 text-[10px] font-extrabold rounded-lg border border-green-200 uppercase">Sudah Dinilai</span>
                @endif
            </span>
        </div>
    </div>

    <!-- Score Circle -->
    <div class="mb-6">
        @if($hasil->status === 'dinilai')
            <div class="inline-flex flex-col items-center justify-center w-36 h-36 rounded-full border-8 {{ $hasil->nilai_akhir >= $kkm ? 'border-green-100 bg-green-50/20' : 'border-red-100 bg-red-50/20' }} shadow-sm">
                <span class="text-3xl font-black {{ $hasil->nilai_akhir >= $kkm ? 'text-green-700' : 'text-red-700' }}">{{ $hasil->nilai_akhir }}</span>
                <span class="text-[10px] font-extrabold text-slate-400 uppercase mt-1">Skor Ujian</span>
            </div>
            
            <div class="mt-4 text-xs font-bold {{ $hasil->nilai_akhir >= $kkm ? 'text-green-600' : 'text-red-600' }}">
                @if($hasil->nilai_akhir >= $kkm)
                    Selamat! Anda berhasil mencapai KKM ({{ $kkm }}).
                @else
                    Nilai Anda masih di bawah KKM ({{ $kkm }}). Tetap semangat belajar!
                @endif
            </div>
        @else
            <!-- Displaying pending state for essay exams -->
            <div class="inline-flex flex-col items-center justify-center w-36 h-36 rounded-full border-8 border-slate-100 bg-slate-50/50 shadow-sm">
                <svg class="w-8 h-8 text-amber-500 animate-bounce mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span class="text-[10px] font-black text-slate-500 uppercase text-center max-w-[100px] leading-tight">Menunggu Koreksi</span>
            </div>
        @endif
    </div>

    <!-- Back Button -->
    <a href="{{ route('siswa.ujian.index') }}" class="block w-full text-center bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-3 rounded-xl border border-slate-200 transition-all active:scale-[0.98] text-sm min-h-[44px] flex items-center justify-center">
        Kembali ke Dashboard
    </a>
</div>

@endsection
