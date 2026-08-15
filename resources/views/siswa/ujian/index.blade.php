@extends('layouts.app')
@section('title', 'Ujian Harian')
@section('page_title', 'Ujian Harian')
@section('content')

@if(session('success'))
<div class="mb-4 bg-green-100 text-green-800 p-3.5 rounded-lg text-sm border border-green-200 shadow-sm">
    {{ session('success') }}
</div>
@endif

@if(session('info'))
<div class="mb-4 bg-blue-100 text-blue-800 p-3.5 rounded-lg text-sm border border-blue-200 shadow-sm">
    {{ session('info') }}
</div>
@endif

@if($errors->any())
<div class="mb-4 bg-red-100 text-red-800 p-3.5 rounded-lg text-sm border border-red-200 shadow-sm">
    @foreach ($errors->all() as $error)
        <p class="font-semibold">{{ $error }}</p>
    @endforeach
</div>
@endif

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    
    <!-- Left/Main: Active Exam Card -->
    <div class="md:col-span-2 space-y-6">
        
        <!-- Sedang Mengerjakan / Ujian Aktif -->
        @if($sedangMengerjakan)
        <div class="bg-amber-50 rounded-xl border-2 border-amber-300 p-5 shadow-sm">
            <div class="flex items-center gap-3 mb-3">
                <span class="relative flex h-3 w-3">
                  <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                  <span class="relative inline-flex rounded-full h-3 w-3 bg-amber-500"></span>
                </span>
                <h3 class="font-extrabold text-amber-800 text-base">Ujian Sedang Berlangsung!</h3>
            </div>
            <p class="text-sm text-amber-700 mb-4 font-semibold">Anda masih memiliki sesi ujian aktif. Silakan masuk kembali ke ruang ujian untuk melanjutkan pengerjaan.</p>
            
            <div class="p-4 bg-white rounded-lg border border-amber-200 mb-4">
                <div class="font-bold text-slate-800">{{ $sedangMengerjakan->ujian->judul }}</div>
                <div class="text-xs text-slate-500 mt-1">Materi/Bab: {{ $sedangMengerjakan->ujian->bab }} | Durasi: {{ $sedangMengerjakan->ujian->durasi }} Menit</div>
            </div>

            <a href="{{ route('siswa.ujian.kerjakan', $sedangMengerjakan->ujian_id) }}" class="block w-full text-center bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 px-4 rounded-xl shadow-md transition-all active:scale-[0.98] text-base min-h-[48px] flex items-center justify-center">
                Lanjutkan Mengerjakan
            </a>
        </div>
        @elseif($ujianAktif)
        <div class="bg-white rounded-xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-center gap-2 mb-4">
                <span class="inline-block w-2.5 h-2.5 bg-green-500 rounded-full"></span>
                <h3 class="font-extrabold text-slate-800 text-base">Ujian Harian Tersedia</h3>
            </div>

            <div class="p-4 bg-slate-50 rounded-lg border border-slate-100 mb-5">
                <div class="font-bold text-slate-800 text-base">{{ $ujianAktif->judul }}</div>
                <div class="text-xs text-slate-500 mt-1">Materi/Bab: {{ $ujianAktif->bab }} | Durasi: {{ $ujianAktif->durasi }} Menit</div>
                <div class="text-xs text-slate-500">Tanggal: {{ \Carbon\Carbon::parse($ujianAktif->tanggal)->translatedFormat('d F Y') }}</div>
            </div>

            <form action="{{ route('siswa.ujian.masuk', $ujianAktif->id) }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Masukkan Token Ujian</label>
                    <input type="text" name="token" required maxlength="6" placeholder="Ketik 6 digit token..." class="w-full bg-slate-50 border border-slate-300 rounded-xl px-4 py-3 text-center font-mono font-extrabold text-lg text-slate-800 tracking-[0.25em] focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all uppercase placeholder:font-sans placeholder:text-sm placeholder:tracking-normal placeholder:text-slate-400">
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-md transition-all active:scale-[0.98] text-base min-h-[48px] flex items-center justify-center">
                    Mulai Ujian
                </button>
            </form>
        </div>
        @else
        <!-- No Active Exam -->
        <div class="bg-white rounded-xl border border-slate-200 p-8 shadow-sm text-center">
            <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4 border border-slate-200">
                <svg class="w-8 h-8 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 .364l-.707 .707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548 .547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path></svg>
            </div>
            <h3 class="font-extrabold text-slate-800 text-base mb-1">Belum Ada Ujian Aktif</h3>
            <p class="text-sm text-slate-500 max-w-sm mx-auto">Saat ini belum ada jadwal ujian harian aktif untuk kelas Anda ({{ $siswa->kelas->nama_kelas ?? '-' }}).</p>
        </div>
        @endif

    </div>

    <!-- Right: History Card -->
    <div class="bg-white rounded-xl border border-slate-200 p-4 shadow-sm h-fit">
        <h3 class="font-extrabold text-slate-800 text-sm mb-3 pb-2 border-b border-slate-100">Riwayat Ujian</h3>
        
        <div class="space-y-3 max-h-[400px] overflow-y-auto pr-1">
            @forelse($riwayat as $r)
            <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 flex justify-between items-center gap-2">
                <div class="min-w-0">
                    <div class="font-bold text-slate-800 text-xs truncate">{{ $r->ujian->judul }}</div>
                    <div class="text-[10px] text-slate-500 mt-0.5">Bab: {{ $r->ujian->bab }}</div>
                    <div class="text-[10px] text-slate-400">{{ \Carbon\Carbon::parse($r->created_at)->translatedFormat('d M Y') }}</div>
                </div>
                
                <div>
                    @if($r->status === 'selesai')
                    <span class="inline-block px-2 py-1 bg-amber-100 text-amber-800 text-[10px] font-bold rounded-lg border border-amber-200">Koreksi</span>
                    @else
                    <a href="{{ route('siswa.ujian.hasil', $r->ujian_id) }}" class="inline-block px-2.5 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold rounded-lg border border-blue-200 transition-colors shadow-sm whitespace-nowrap">
                        Lihat Nilai
                    </a>
                    @endif
                </div>
            </div>
            @empty
            <div class="text-center py-6 text-slate-400 text-xs italic">Belum ada riwayat ujian.</div>
            @endforelse
        </div>
    </div>

</div>

@endsection
