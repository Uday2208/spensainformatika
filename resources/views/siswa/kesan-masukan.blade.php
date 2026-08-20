@extends('layouts.app')
@section('title', 'Kesan & Masukan')
@section('page_title', '💬 Kesan & Masukan')
@section('content')

@if(session('success'))
<div class="mb-4 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl shadow-xs">
    <div class="w-7 h-7 bg-emerald-500 rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold">✓</div>
    <p class="font-semibold text-sm">{{ session('success') }}</p>
</div>
@endif

@if($errors->any())
<div class="mb-4 flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 p-4 rounded-2xl shadow-xs">
    <div class="w-7 h-7 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold mt-0.5">✕</div>
    <div class="flex-1">
        <strong class="block text-sm font-bold">Gagal Mengirim Masukan:</strong>
        <ul class="list-disc ml-5 text-xs text-red-700 mt-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    
    <!-- Kolom Kiri: Form Kirim Masukan -->
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6 sm:p-7">
            <div class="flex items-center gap-3 pb-4 mb-5 border-b border-slate-100">
                <div class="w-10 h-10 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold">
                    ✍️
                </div>
                <div>
                    <h2 class="text-base font-black text-slate-800">Kirim Kesan & Masukan Pembelajaran</h2>
                    <p class="text-xs text-slate-500">Tuliskan pengalaman belajar, materi favorit, atau saran perbaikan untuk Guru.</p>
                </div>
            </div>

            @if($canComment)
            <form action="{{ url('/app/komentar') }}" method="POST" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Isi Pesan Masukan</label>
                    <textarea name="isi_komentar" 
                              required 
                              maxlength="300" 
                              rows="5" 
                              class="input-compact w-full bg-slate-50 rounded-2xl p-4 text-xs border-slate-200 focus:bg-white text-slate-800 leading-relaxed" 
                              placeholder="Ceritakan pengalaman belajar Informatika, materi yang paling kamu sukai, atau saran untuk pembelajaran ke depan..."></textarea>
                    <p class="text-[11px] text-slate-400 mt-1.5 flex justify-between">
                        <span>Maksimal 300 karakter.</span>
                        <span class="text-slate-400">1x per 7 hari</span>
                    </p>
                </div>
                
                <div class="p-3.5 bg-slate-50 rounded-2xl border border-slate-200/80 flex items-center">
                    <input type="checkbox" name="is_anonim" id="is_anonim" value="1" class="w-4 h-4 text-blue-600 bg-white border-slate-300 rounded focus:ring-blue-500 cursor-pointer">
                    <label for="is_anonim" class="ml-2.5 text-xs font-bold text-slate-700 cursor-pointer">
                        Kirim sebagai Anonim <span class="font-normal text-slate-400">(Nama lengkap Anda akan disembunyikan dari publik)</span>
                    </label>
                </div>
                
                <div class="pt-2 flex justify-end">
                    <button type="submit" class="px-7 py-3 bg-blue-600 hover:bg-blue-700 text-white font-black text-xs rounded-xl shadow-md active:scale-95 transition-all flex items-center gap-2">
                        <span>Kirim Masukan</span>
                        <span>➔</span>
                    </button>
                </div>
            </form>
            @else
            <div class="p-6 bg-amber-50/80 border border-amber-200 rounded-2xl text-center space-y-2">
                <div class="w-12 h-12 bg-amber-100 text-amber-700 rounded-full flex items-center justify-center mx-auto text-xl font-bold mb-2">
                    ⏳
                </div>
                <h3 class="font-black text-slate-800 text-sm">Anda Sudah Mengirim Masukan Minggu Ini</h3>
                <p class="text-xs text-slate-600 max-w-md mx-auto">
                    Terima kasih atas partisipasi Anda! Anda dapat mengirimkan masukan baru kembali dalam <strong class="text-amber-800">{{ $daysRemaining }} hari lagi</strong>.
                </p>
            </div>
            @endif
        </div>
    </div>

    <!-- Kolom Kanan: Riwayat Masukan Saya -->
    <div class="space-y-6">
        <div class="bg-white rounded-3xl border border-slate-200 shadow-sm p-6">
            <h3 class="font-black text-slate-800 text-sm mb-4 pb-3 border-b border-slate-100 flex items-center gap-2">
                <span>📋</span> Riwayat Masukan Saya
            </h3>

            @if($komentars->isEmpty())
            <div class="p-8 text-center bg-slate-50/50 rounded-2xl border border-dashed border-slate-200 text-slate-400">
                <p class="text-xs font-bold text-slate-500">Belum Ada Riwayat Masukan</p>
                <p class="text-[10px] text-slate-400 mt-1">Masukan yang Anda kirimkan akan tersimpan dan tampil di sini.</p>
            </div>
            @else
            <div class="space-y-3.5 max-h-[460px] overflow-y-auto pr-1">
                @foreach($komentars as $k)
                <div class="p-4 rounded-2xl border border-slate-200 bg-slate-50/50 shadow-xs flex flex-col gap-2">
                    <div class="flex items-center justify-between gap-2">
                        <span class="text-[10px] text-slate-400 font-medium">
                            📅 {{ \Carbon\Carbon::parse($k->created_at)->translatedFormat('d M Y, H:i') }}
                        </span>
                        @if($k->is_anonim)
                        <span class="px-2 py-0.5 rounded-md bg-slate-200 text-slate-700 font-bold text-[9px] uppercase">Anonim</span>
                        @else
                        <span class="px-2 py-0.5 rounded-md bg-blue-100 text-blue-800 font-bold text-[9px] uppercase">Publik</span>
                        @endif
                    </div>
                    <p class="text-xs text-slate-700 font-medium leading-relaxed italic">
                        "{{ $k->isi_komentar }}"
                    </p>
                </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

</div>

@endsection
