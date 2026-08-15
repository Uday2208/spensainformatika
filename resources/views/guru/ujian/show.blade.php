@extends('layouts.app')
@section('title', 'Detail & Setting Ujian')
@section('page_title', 'Detail & Setting Ujian')
@section('content')

@if(session('success'))
<div class="mb-4 bg-green-100 text-green-800 p-3.5 rounded-xl text-sm border border-green-200 shadow-sm flex items-center gap-2">
    <svg class="w-5 h-5 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
    <span>{{ session('success') }}</span>
</div>
@endif

@if($errors->any())
<div class="mb-4 bg-red-100 text-red-800 p-3.5 rounded-xl text-sm border border-red-200 shadow-sm">
    @foreach ($errors->all() as $error)
        <p>• {{ $error }}</p>
    @endforeach
</div>
@endif

<div x-data="{ 
    settingModal: false,
    addSoalModal: false,
    editSoalModal: false,
    tokenOption: 'keep',
    soalTipe: 'pg',
    editSoalData: { id: '', tipe: 'pg', pertanyaan: '', opsi_a: '', opsi_b: '', opsi_c: '', opsi_d: '', jawaban_benar: 'a', bobot: 5 }
}">

    <!-- Top Action Bar -->
    <div class="mb-6 bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-sm flex flex-wrap items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2 mb-1">
                <span class="text-xs bg-slate-100 text-slate-600 font-extrabold px-2.5 py-0.5 rounded-md border border-slate-200 uppercase">{{ $ujian->bab }}</span>
                @if($ujian->isDraft())
                <span class="px-2.5 py-0.5 bg-slate-100 text-slate-700 text-xs font-bold rounded-full border border-slate-200">Status: Draft</span>
                @elseif($ujian->isAktif())
                <span class="px-2.5 py-0.5 bg-green-100 text-green-700 text-xs font-bold rounded-full border border-green-200 animate-pulse">Status: Aktif Ujian</span>
                @else
                <span class="px-2.5 py-0.5 bg-amber-100 text-amber-700 text-xs font-bold rounded-full border border-amber-200">Status: Selesai</span>
                @endif
            </div>
            <h2 class="text-xl font-black text-slate-800">{{ $ujian->judul }}</h2>
        </div>

        <div class="flex items-center gap-2.5 flex-wrap">
            <button @click="settingModal = true" class="bg-blue-600 hover:bg-blue-700 text-white font-extrabold px-4 py-2.5 rounded-xl text-sm shadow-md transition-all active:scale-[0.98] flex items-center gap-2 border border-blue-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                ⚙️ Setting Ujian & Kelas Aktif
            </button>

            @if($ujian->isAktif())
            <a href="{{ route('guru.ujian.monitoring', $ujian->id) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold px-4 py-2.5 rounded-xl text-sm shadow-md transition-all active:scale-[0.98] flex items-center gap-2 border border-emerald-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                Monitor Ujian
            </a>
            <form action="{{ route('guru.ujian.finish', $ujian->id) }}" method="POST" onsubmit="return confirm('Akhiri ujian ini?');" class="inline">
                @csrf
                <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white font-extrabold px-4 py-2.5 rounded-xl text-sm shadow-md transition-all active:scale-[0.98]">
                    Akhiri Ujian
                </button>
            </form>
            @endif
        </div>
    </div>

    <!-- Grid Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Left: Summary Info Card -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 h-fit space-y-4">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                <h3 class="font-extrabold text-slate-800 text-base">Parameter Ujian</h3>
                <button @click="settingModal = true" class="text-blue-600 hover:text-blue-800 text-xs font-extrabold flex items-center gap-1 bg-blue-50 px-2.5 py-1 rounded-lg border border-blue-100">
                    Edit Setting
                </button>
            </div>

            <div class="space-y-3.5 text-sm">
                <div>
                    <label class="text-slate-400 text-xs font-semibold block">Tanggal Pelaksanaan</label>
                    <span class="font-bold text-slate-800">
                        {{ $ujian->tanggal ? \Carbon\Carbon::parse($ujian->tanggal)->translatedFormat('l, d F Y') : 'Belum diatur (Atur di Setting)' }}
                    </span>
                </div>
                
                <div>
                    <label class="text-slate-400 text-xs font-semibold block">Durasi Pengerjaan</label>
                    <span class="font-bold text-slate-800">{{ $ujian->durasi }} Menit</span>
                </div>

                <div>
                    <label class="text-slate-400 text-xs font-semibold block mb-1">Kelas Aktif Ujian (Peserta)</label>
                    @if($ujian->kelasList->count() > 0)
                    <div class="flex flex-wrap gap-1.5">
                        @foreach($ujian->kelasList as $kelas)
                        <span class="px-2.5 py-1 bg-blue-50 text-blue-800 text-xs font-black rounded-lg border border-blue-200 flex items-center gap-1">
                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                            {{ $kelas->nama_kelas }}
                        </span>
                        @endforeach
                    </div>
                    @else
                    <span class="text-xs text-amber-700 bg-amber-50 px-2 py-1 rounded border border-amber-200 font-bold block">
                        ⚠️ Belum ada kelas yang diaktifkan
                    </span>
                    @endif
                </div>

                <div>
                    <label class="text-slate-400 text-xs font-semibold block mb-1">Token Ujian Masuk</label>
                    @if($ujian->token)
                    <div class="bg-slate-900 text-yellow-400 font-mono font-black text-lg px-3 py-1.5 rounded-xl inline-block tracking-widest border border-slate-800 shadow-inner">
                        {{ $ujian->token }}
                    </div>
                    @else
                    <span class="text-xs text-slate-400 font-medium italic block">Belum ada token (Atur di Setting Ujian)</span>
                    @endif
                </div>
            </div>

            <div class="pt-3 border-t border-slate-100">
                <a href="{{ route('guru.ujian.index') }}" class="w-full py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-sm rounded-xl transition-all text-center block">
                    ← Kembali ke Daftar Ujian
                </a>
            </div>
        </div>

        <!-- Right: Daftar Soal -->
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100 mb-4">
                <div>
                    <h3 class="font-extrabold text-slate-800 text-base">Bank Soal Ujian</h3>
                    <p class="text-xs text-slate-400 font-medium">Total: {{ $ujian->soals->count() }} Soal (PG: {{ $ujian->soals->where('tipe', 'pg')->count() }}, Essay: {{ $ujian->soals->where('tipe', 'essay')->count() }})</p>
                </div>
                @if($ujian->isDraft())
                <button @click="addSoalModal = true" class="bg-blue-600 hover:bg-blue-700 text-white font-extrabold flex items-center gap-1.5 text-xs py-2 px-3.5 rounded-xl shadow-md transition-all active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Soal
                </button>
                @endif
            </div>

            <div class="space-y-4">
                @forelse($ujian->soals as $index => $soal)
                <div class="p-4 rounded-xl border {{ $soal->tipe === 'pg' ? 'border-slate-200 bg-slate-50/50' : 'border-dashed border-indigo-200 bg-indigo-50/20' }}">
                    <div class="flex justify-between items-start mb-2 gap-4">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 bg-slate-200 text-slate-800 rounded-full text-xs font-black flex items-center justify-center">{{ $index + 1 }}</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-black uppercase {{ $soal->tipe === 'pg' ? 'bg-blue-100 text-blue-800 border border-blue-200' : 'bg-indigo-100 text-indigo-800 border border-indigo-200' }}">
                                {{ $soal->tipe }}
                            </span>
                            <span class="text-xs text-slate-500 font-bold">Bobot: {{ $soal->bobot }}</span>
                        </div>
                        @if($ujian->isDraft())
                        <div class="flex items-center gap-1">
                            <button type="button" 
                                @click="
                                    editSoalData = { 
                                        id: '{{ $soal->id }}', 
                                        tipe: '{{ $soal->tipe }}', 
                                        pertanyaan: {{ json_encode($soal->pertanyaan ?? '') }}, 
                                        opsi_a: {{ json_encode($soal->opsi_a ?? '') }}, 
                                        opsi_b: {{ json_encode($soal->opsi_b ?? '') }}, 
                                        opsi_c: {{ json_encode($soal->opsi_c ?? '') }}, 
                                        opsi_d: {{ json_encode($soal->opsi_d ?? '') }}, 
                                        jawaban_benar: '{{ $soal->jawaban_benar }}', 
                                        bobot: '{{ $soal->bobot }}' 
                                    }; 
                                    editSoalModal = true;
                                "
                                class="text-blue-600 hover:text-blue-800 p-1.5 hover:bg-white rounded-lg transition-all" title="Edit Soal">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>
                            <form action="{{ route('guru.ujian.soal.destroy', [$ujian->id, $soal->id]) }}" method="POST" onsubmit="return confirm('Hapus soal ini?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 p-1.5 hover:bg-white rounded-lg transition-all" title="Hapus Soal">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>

                    <div class="text-slate-800 text-sm font-semibold mb-3 leading-relaxed">{!! nl2br(e($soal->pertanyaan)) !!}</div>

                    @if($soal->tipe === 'pg')
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs text-slate-700 pl-4 sm:pl-8">
                        <div class="p-2 rounded-lg border {{ $soal->jawaban_benar === 'a' ? 'bg-green-100 text-green-900 border-green-300 font-bold' : 'border-slate-200 bg-white' }}">
                            <span class="font-extrabold uppercase mr-1">A.</span> {{ $soal->opsi_a }}
                        </div>
                        <div class="p-2 rounded-lg border {{ $soal->jawaban_benar === 'b' ? 'bg-green-100 text-green-900 border-green-300 font-bold' : 'border-slate-200 bg-white' }}">
                            <span class="font-extrabold uppercase mr-1">B.</span> {{ $soal->opsi_b }}
                        </div>
                        <div class="p-2 rounded-lg border {{ $soal->jawaban_benar === 'c' ? 'bg-green-100 text-green-900 border-green-300 font-bold' : 'border-slate-200 bg-white' }}">
                            <span class="font-extrabold uppercase mr-1">C.</span> {{ $soal->opsi_c }}
                        </div>
                        <div class="p-2 rounded-lg border {{ $soal->jawaban_benar === 'd' ? 'bg-green-100 text-green-900 border-green-300 font-bold' : 'border-slate-200 bg-white' }}">
                            <span class="font-extrabold uppercase mr-1">D.</span> {{ $soal->opsi_d }}
                        </div>
                    </div>
                    @endif
                </div>
                @empty
                <div class="border border-dashed border-slate-300 p-10 rounded-2xl text-center text-slate-400 text-sm font-semibold">
                    Belum ada soal ditambahkan. Klik tombol <span class="text-blue-600 font-bold">+ Tambah Soal</span> untuk menambah pertanyaan.
                </div>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Modal: SETTING UJIAN & AKTIVASI KELAS -->
    <div x-show="settingModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-60 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl p-6 shadow-2xl w-full max-w-lg" @click.away="settingModal = false">
            <div class="flex items-center justify-between pb-3 border-b border-slate-100 mb-4">
                <h3 class="font-black text-slate-800 text-lg flex items-center gap-2">
                    <span>⚙️</span> Setting Ujian & Aktivasi Kelas
                </h3>
                <button @click="settingModal = false" class="text-slate-400 hover:text-slate-600 p-1 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <form action="{{ route('guru.ujian.setting', $ujian->id) }}" method="POST" class="space-y-4">
                @csrf
                
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Pelaksanaan</label>
                        <input type="date" name="tanggal" value="{{ old('tanggal', $ujian->tanggal ? \Carbon\Carbon::parse($ujian->tanggal)->format('Y-m-d') : date('Y-m-d')) }}" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-semibold focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Durasi Pengerjaan (Menit)</label>
                        <input type="number" name="durasi" required min="5" max="180" value="{{ old('durasi', $ujian->durasi ?: 60) }}" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-extrabold text-center focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Selection of Active Classes -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pilih Kelas yang Diaktifkan (Peserta Ujian)</label>
                    <p class="text-[11px] text-slate-400 mb-2">Hanya siswa dari kelas yang dicentang di bawah ini yang dapat melihat & mengerjakan ujian.</p>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 bg-slate-50 p-3 rounded-xl border border-slate-200 max-h-36 overflow-y-auto">
                        @php $currentKelasIds = $ujian->kelasList->pluck('id')->toArray(); @endphp
                        @foreach($allKelas as $k)
                        <label class="flex items-center gap-2 cursor-pointer py-1 px-1.5 rounded hover:bg-white select-none transition-all">
                            <input type="checkbox" name="kelas_ids[]" value="{{ $k->id }}" {{ in_array($k->id, $currentKelasIds) ? 'checked' : '' }} class="w-4 h-4 text-blue-600 rounded border-slate-300 focus:ring-blue-500 cursor-pointer">
                            <span class="text-xs font-bold text-slate-700">{{ $k->nama_kelas }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <!-- Token Options -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Pengaturan Token Ujian</label>
                    <div class="space-y-2 bg-slate-50 p-3 rounded-xl border border-slate-200">
                        <label class="flex items-center gap-2.5 cursor-pointer text-xs font-semibold text-slate-700">
                            <input type="radio" name="token_option" value="keep" x-model="tokenOption" class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-500">
                            <span>Tetap gunakan Token saat ini: <strong class="text-blue-700 font-mono font-bold">{{ $ujian->token ?: 'Belum Ada' }}</strong></span>
                        </label>

                        <label class="flex items-center gap-2.5 cursor-pointer text-xs font-semibold text-slate-700">
                            <input type="radio" name="token_option" value="random" x-model="tokenOption" class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-500">
                            <span>Generate Token Acak 6 Digit Baru</span>
                        </label>

                        <label class="flex items-center gap-2.5 cursor-pointer text-xs font-semibold text-slate-700">
                            <input type="radio" name="token_option" value="custom" x-model="tokenOption" class="w-4 h-4 text-blue-600 border-slate-300 focus:ring-blue-500">
                            <span>Ketik Token Custom Sendiri (6 Karakter)</span>
                        </label>

                        <div x-show="tokenOption === 'custom'" class="pt-2 pl-6" style="display: none;">
                            <input type="text" name="custom_token" maxlength="6" placeholder="Contoh: MATH7A" class="bg-white border border-slate-300 rounded-lg px-3 py-1.5 text-xs font-mono font-bold uppercase tracking-widest text-slate-800 focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>

                <!-- Status Select -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Status Ujian</label>
                    <select name="status" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 cursor-pointer focus:ring-2 focus:ring-blue-500">
                        <option value="draft" {{ $ujian->isDraft() ? 'selected' : '' }}>📝 Draft (Belum bisa diakses siswa)</option>
                        <option value="aktif" {{ $ujian->isAktif() ? 'selected' : '' }}>🟢 Aktif (Kelas terpilih & token valid bisa akses)</option>
                        <option value="selesai" {{ $ujian->isSelesai() ? 'selected' : '' }}>⏹️ Selesai (Ujian diakhiri)</option>
                    </select>
                </div>

                <div class="flex justify-end gap-2 border-t border-slate-100 pt-4">
                    <button type="button" @click="settingModal = false" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold transition-all">Batal</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white shadow-md px-5 py-2 rounded-xl text-xs font-extrabold transition-all active:scale-[0.98]">
                        Simpan Setting Ujian ➔
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Tambah Soal -->
    <div x-show="addSoalModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-60 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl p-6 shadow-2xl w-full max-w-lg" @click.away="addSoalModal = false">
            <h3 class="font-black text-slate-800 text-lg mb-4 border-b border-slate-100 pb-2">Tambah Soal Ujian</h3>
            <form action="{{ route('guru.ujian.soal.store', $ujian->id) }}" method="POST">
                @csrf
                
                <div class="mb-3 grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Soal</label>
                        <select name="tipe" x-model="soalTipe" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-bold cursor-pointer">
                            <option value="pg">Pilihan Ganda (PG)</option>
                            <option value="essay">Uraian / Essay</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Bobot Soal</label>
                        <input type="number" name="bobot" required min="1" max="100" value="5" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs text-center font-black">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pertanyaan / Soal</label>
                    <textarea name="pertanyaan" required rows="3" placeholder="Ketikkan isi pertanyaan disini..." class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all"></textarea>
                </div>

                <!-- Fields for PG only -->
                <div x-show="soalTipe === 'pg'" class="space-y-2 border-t border-slate-100 pt-3 mb-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-0.5">Opsi A</label>
                            <input type="text" name="opsi_a" ::required="soalTipe === 'pg'" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-2.5 py-1.5 text-xs">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-0.5">Opsi B</label>
                            <input type="text" name="opsi_b" ::required="soalTipe === 'pg'" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-2.5 py-1.5 text-xs">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-0.5">Opsi C</label>
                            <input type="text" name="opsi_c" ::required="soalTipe === 'pg'" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-2.5 py-1.5 text-xs">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-0.5">Opsi D</label>
                            <input type="text" name="opsi_d" ::required="soalTipe === 'pg'" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-2.5 py-1.5 text-xs">
                        </div>
                    </div>
                    <div class="w-1/2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jawaban Benar</label>
                        <select name="jawaban_benar" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-bold cursor-pointer">
                            <option value="a">Opsi A</option>
                            <option value="b">Opsi B</option>
                            <option value="c">Opsi C</option>
                            <option value="d">Opsi D</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-2 border-t border-slate-100 pt-3">
                    <button type="button" @click="addSoalModal = false" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold transition-all">Batal</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-extrabold px-5 py-2 rounded-xl text-xs shadow-md transition-all active:scale-[0.98]">Simpan Soal</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal: Edit Soal -->
    <div x-show="editSoalModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto bg-black bg-opacity-60 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl p-6 shadow-2xl w-full max-w-lg" @click.away="editSoalModal = false">
            <h3 class="font-black text-slate-800 text-lg mb-4 border-b border-slate-100 pb-2">Ubah Soal Ujian</h3>
            <form :action="`{{ url('/app/ujian') }}/${{{ $ujian->id }}}/soal/${editSoalData.id}`" method="POST">
                @csrf
                @method('PUT')
                
                <div class="mb-3 grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tipe Soal</label>
                        <input type="text" x-model="editSoalData.tipe" readonly class="w-full bg-slate-100 border border-slate-200 rounded-xl px-3 py-2 text-xs font-extrabold text-center uppercase cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Bobot Soal</label>
                        <input type="number" name="bobot" x-model.number="editSoalData.bobot" required min="1" max="100" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-black text-center">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="block text-xs font-bold text-slate-700 mb-1">Pertanyaan / Soal</label>
                    <textarea name="pertanyaan" x-model="editSoalData.pertanyaan" required rows="3" class="w-full bg-slate-50 border border-slate-300 rounded-xl p-3 text-xs font-medium focus:bg-white focus:ring-2 focus:ring-blue-500 transition-all"></textarea>
                </div>

                <!-- Fields for PG only -->
                <div x-show="editSoalData.tipe === 'pg'" class="space-y-2 border-t border-slate-100 pt-3 mb-4">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-0.5">Opsi A</label>
                            <input type="text" name="opsi_a" x-model="editSoalData.opsi_a" ::required="editSoalData.tipe === 'pg'" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-2.5 py-1.5 text-xs">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-0.5">Opsi B</label>
                            <input type="text" name="opsi_b" x-model="editSoalData.opsi_b" ::required="editSoalData.tipe === 'pg'" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-2.5 py-1.5 text-xs">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-0.5">Opsi C</label>
                            <input type="text" name="opsi_c" x-model="editSoalData.opsi_c" ::required="editSoalData.tipe === 'pg'" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-2.5 py-1.5 text-xs">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-500 mb-0.5">Opsi D</label>
                            <input type="text" name="opsi_d" x-model="editSoalData.opsi_d" ::required="editSoalData.tipe === 'pg'" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-2.5 py-1.5 text-xs">
                        </div>
                    </div>
                    <div class="w-1/2">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Jawaban Benar</label>
                        <select name="jawaban_benar" x-model="editSoalData.jawaban_benar" class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-2 text-xs font-bold cursor-pointer">
                            <option value="a">Opsi A</option>
                            <option value="b">Opsi B</option>
                            <option value="c">Opsi C</option>
                            <option value="d">Opsi D</option>
                        </select>
                    </div>
                </div>

                <div class="flex justify-end gap-2 border-t border-slate-100 pt-3">
                    <button type="button" @click="editSoalModal = false" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-xl text-xs font-bold transition-all">Batal</button>
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-extrabold px-5 py-2 rounded-xl text-xs shadow-md transition-all active:scale-[0.98]">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

</div>

@endsection
