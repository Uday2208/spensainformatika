@extends('layouts.app')
@section('title', 'Tugas Kelas (Rombel)')
@section('page_title', 'Tugas Kelas (Rombel)')
@section('content')

@if(session('success'))
<div class="mb-4 flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl shadow-xs">
    <div class="w-7 h-7 bg-emerald-500 rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold text-xs">
        ✓
    </div>
    <span class="font-semibold text-sm">{{ session('success') }}</span>
</div>
@endif

@if($errors->any())
<div class="mb-4 flex items-start gap-3 bg-red-50 border border-red-200 text-red-800 p-4 rounded-2xl shadow-xs">
    <div class="w-7 h-7 bg-red-500 rounded-full flex items-center justify-center flex-shrink-0 text-white font-bold text-xs mt-0.5">
        ✕
    </div>
    <div>
        <strong class="font-bold block mb-1 text-sm">Terjadi Kesalahan:</strong>
        <ul class="list-disc ml-5 text-xs text-red-700">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

{{-- Banner Card --}}
<div class="flex items-start gap-4 bg-gradient-to-r from-blue-900 via-indigo-900 to-blue-800 rounded-2xl p-5 sm:p-6 mb-6 shadow-xl relative overflow-hidden text-white">
    <div class="absolute inset-0 opacity-10 pointer-events-none">
        <div class="absolute top-0 right-0 w-64 h-64 bg-white rounded-full -translate-y-24 translate-x-24"></div>
    </div>
    <div class="w-12 h-12 bg-blue-500/80 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg text-white font-bold text-2xl">
        🏫
    </div>
    <div class="relative flex-1 min-w-0">
        <h2 class="text-white font-bold text-base sm:text-lg leading-tight">Penugasan Rombel Kelas</h2>
        <p class="text-blue-200 text-xs sm:text-sm mt-1 leading-relaxed">
            Terbitkan tugas belajar untuk <strong>satu rombel</strong>, <strong>beberapa rombel sekaligus</strong>, atau <strong>seluruh kelas binaan Anda</strong> dalam satu kali klik.
        </p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Form Buat Tugas Kelas -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sticky top-6"
             x-data="{ 
                 allKelasIds: {{ json_encode($kelas->pluck('id')) }},
                 selectedKelas: [],
                 toggleAll() {
                     if (this.selectedKelas.length === this.allKelasIds.length) {
                         this.selectedKelas = [];
                     } else {
                         this.selectedKelas = [...this.allKelasIds];
                     }
                 }
             }">
            <h3 class="font-bold text-slate-800 mb-4 border-b border-slate-100 pb-3 flex items-center gap-2 text-sm">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Buat Tugas Kelas Baru
            </h3>
            
            <form action="{{ url('/app/tugas/kelas') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Judul Tugas <span class="text-red-500">*</span></label>
                    <input type="text" name="judul" required class="input-compact w-full bg-slate-50 min-h-[40px] rounded-xl" placeholder="Contoh: Tugas Praktikum Pemrograman Web">
                </div>

                <!-- Pemilih Rombel Kelas (Multi-Checkboxes) -->
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider">Target Kelas <span class="text-red-500">*</span></label>
                        <button type="button" @click="toggleAll()" class="text-[11px] font-bold text-blue-600 hover:text-blue-800 transition-colors">
                            <span x-text="selectedKelas.length === allKelasIds.length ? 'Batal Pilih Semua' : 'Pilih Semua Kelas'"></span>
                        </button>
                    </div>

                    <div class="space-y-1.5 max-h-48 overflow-y-auto p-2.5 bg-slate-50 border border-slate-200 rounded-xl">
                        @foreach($kelas as $k)
                        <label class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-white transition-colors cursor-pointer border border-transparent hover:border-slate-200">
                            <input type="checkbox" name="kelas_ids[]" value="{{ $k->id }}" x-model="selectedKelas" class="rounded text-blue-600 focus:ring-blue-500 w-4 h-4">
                            <span class="text-xs font-semibold text-slate-700">Kelas {{ $k->nama_kelas }}</span>
                        </label>
                        @endforeach
                    </div>
                    <p class="text-[11px] text-slate-400 mt-1">Bisa pilih 1, beberapa, atau semua kelas yang Anda ampu.</p>
                </div>

                <!-- Tenggat Waktu / Deadline -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Batas Waktu (Deadline Opsional)</label>
                    <input type="datetime-local" name="deadline" class="input-compact w-full bg-slate-50 min-h-[40px] rounded-xl text-xs">
                </div>

                <!-- Deskripsi / Petunjuk -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Petunjuk Pengerjaan</label>
                    <textarea name="deskripsi" rows="4" class="input-compact w-full bg-slate-50 rounded-xl text-xs" placeholder="Tuliskan petunjuk pengerjaan tugas, instruksi tugas, format pengumpulan..."></textarea>
                </div>

                <!-- Dokumen Lampiran -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Lampiran Dokumen / Modul (Opsional)</label>
                    <input type="file" name="file_tugas" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.jpg,.jpeg,.png" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl p-1 bg-slate-50">
                    <p class="text-[11px] text-slate-400 mt-1">Maks 10MB (PDF, Word, Excel, PPT, Zip, Gambar).</p>
                </div>

                <!-- Link Eksternal -->
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Tautan Referensi Eksternal (Opsional)</label>
                    <input type="url" name="link" class="input-compact w-full bg-slate-50 min-h-[40px] rounded-xl text-xs" placeholder="https://drive.google.com/... atau YouTube">
                </div>
                
                <button type="submit" 
                        :disabled="selectedKelas.length === 0"
                        :class="selectedKelas.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-blue-700'"
                        class="btn-compact w-full bg-blue-600 text-white shadow-sm font-bold text-xs min-h-[42px] rounded-xl transition-all flex justify-center items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    Terbitkan Tugas Kelas
                </button>
            </form>
        </div>
    </div>

    <!-- Daftar Tugas Kelas -->
    <div class="lg:col-span-2 space-y-4">
        <!-- Filter Header -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4 flex flex-wrap gap-3 justify-between items-center">
            <div>
                <h3 class="font-bold text-slate-800 text-sm">Daftar Tugas Kelas Diterbitkan</h3>
                <p class="text-xs text-slate-500 mt-0.5">Tugas yang aktif dibagikan ke rombel kelas.</p>
            </div>
            
            <form action="{{ url('/app/tugas/kelas') }}" method="GET" class="flex items-center gap-2">
                <select name="kelas_id" onchange="this.form.submit()" class="text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-1.5 font-semibold text-slate-700">
                    <option value="">Semua Rombel</option>
                    @foreach($kelas as $k)
                        <option value="{{ $k->id }}" {{ request('kelas_id') == $k->id ? 'selected' : '' }}>Kelas {{ $k->nama_kelas }}</option>
                    @endforeach
                </select>
                @if(request('kelas_id'))
                <a href="{{ url('/app/tugas/kelas') }}" class="text-xs text-red-600 hover:underline font-semibold">Reset</a>
                @endif
            </form>
        </div>

        <!-- List Tugas -->
        <div class="space-y-3">
            @forelse($tugasList as $tugas)
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 hover:shadow-md transition-all">
                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3">
                    <div class="flex-1 min-w-0">
                        <!-- Badges Rombel -->
                        <div class="flex flex-wrap items-center gap-1.5 mb-2">
                            <span class="text-[11px] font-bold text-slate-400 mr-1">Target:</span>
                            @if($tugas->kelases && $tugas->kelases->count() > 0)
                                @foreach($tugas->kelases as $kTarget)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    Kelas {{ $kTarget->nama_kelas }}
                                </span>
                                @endforeach
                            @elseif($tugas->kelas)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                    Kelas {{ $tugas->kelas->nama_kelas }}
                                </span>
                            @endif

                            @if($tugas->deadline)
                                @php
                                    $isOverdue = \Carbon\Carbon::now()->isAfter($tugas->deadline);
                                @endphp
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-[11px] font-bold {{ $isOverdue ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200' }}">
                                    <span>⏰ Batas:</span>
                                    <span>{{ \Carbon\Carbon::parse($tugas->deadline)->translatedFormat('d M Y, H:i') }}</span>
                                    @if($isOverdue)
                                    <span class="text-[10px] uppercase font-black">(Selesai)</span>
                                    @endif
                                </span>
                            @endif
                        </div>

                        <h4 class="text-base font-bold text-slate-800 leading-snug">{{ $tugas->judul }}</h4>
                        
                        @if($tugas->deskripsi)
                        <p class="text-xs text-slate-600 mt-2 whitespace-pre-line leading-relaxed bg-slate-50 p-3 rounded-xl border border-slate-100">{{ $tugas->deskripsi }}</p>
                        @endif

                        <!-- Badges Lampiran & Link -->
                        <div class="flex flex-wrap items-center gap-2.5 mt-3 pt-2 border-t border-slate-100">
                            @if($tugas->file_tugas)
                            <a href="{{ $tugas->file_url }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-700 rounded-xl text-xs font-semibold transition-colors border border-slate-200">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                <span>Unduh Berkas Tugas</span>
                            </a>
                            @endif

                            @if($tugas->link)
                            <a href="{{ $tugas->link }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-blue-50 hover:bg-blue-100 text-blue-700 rounded-xl text-xs font-semibold transition-colors border border-blue-200">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                <span>Buka Tautan Materi</span>
                            </a>
                            @endif

                            <span class="text-[11px] text-slate-400 font-medium ml-auto">
                                {{ \Carbon\Carbon::parse($tugas->created_at)->translatedFormat('d M Y, H:i') }}
                            </span>
                        </div>
                    </div>

                    <!-- Tombol Hapus -->
                    <div class="sm:self-center flex-shrink-0">
                        <form action="{{ url('/app/tugas/' . $tugas->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus tugas ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 bg-red-50 text-red-600 hover:bg-red-100 rounded-xl transition-colors inline-flex items-center gap-1 text-xs font-bold" title="Hapus Tugas">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                <span class="sm:hidden">Hapus</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-2xl border border-dashed border-slate-300 p-12 text-center shadow-xs">
                <div class="w-14 h-14 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-3 border border-blue-100 text-2xl font-bold">
                    🏫
                </div>
                <h3 class="font-bold text-slate-800 text-sm mb-1">Belum Ada Tugas Kelas</h3>
                <p class="text-xs text-slate-500 max-w-sm mx-auto">
                    Pilih satu atau beberapa rombel kelas di samping untuk menerbitkan tugas baru.
                </p>
            </div>
            @endforelse
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-4">
            {{ $tugasList->links() }}
        </div>
    </div>
</div>

@endsection
