@extends('layouts.app')
@section('title', 'Pengaturan Aplikasi')
@section('page_title', 'Pengaturan Aplikasi')

@section('content')
<div class="space-y-6">

    {{-- Banner Header --}}
    <div class="relative overflow-hidden bg-gradient-to-r from-blue-900 via-blue-800 to-indigo-900 rounded-2xl p-5 shadow-xl text-white">
        <div class="absolute inset-0 opacity-10 pointer-events-none">
            <div class="absolute -top-10 -right-10 w-48 h-48 bg-white rounded-full blur-2xl"></div>
            <div class="absolute -bottom-10 left-20 w-40 h-40 bg-blue-400 rounded-full blur-xl"></div>
        </div>
        <div class="relative flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-blue-500/30 backdrop-blur-md border border-blue-400/30 rounded-2xl flex items-center justify-center shadow-inner flex-shrink-0 text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-xl font-bold tracking-tight">Pengaturan & Konfigurasi Sistem</h2>
                    <p class="text-blue-200 text-xs sm:text-sm mt-0.5">Kelola identitas website, standar nilai akademik KKM, dan konfigurasi komentar</p>
                </div>
            </div>
            <div class="hidden sm:flex items-center gap-2 px-3 py-1.5 bg-white/10 backdrop-blur-sm border border-white/10 rounded-xl text-xs text-blue-100 font-medium">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                Admin Master Settings
            </div>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
    <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200 text-emerald-800 p-4 rounded-2xl shadow-sm transition-all">
        <div class="w-8 h-8 bg-emerald-500 rounded-xl flex items-center justify-center flex-shrink-0 text-white font-bold shadow-sm">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
            </svg>
        </div>
        <div class="flex-1">
            <p class="font-bold text-sm text-emerald-900">Berhasil!</p>
            <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
        </div>
    </div>
    @endif

    @if($errors->any())
    <div class="flex items-start gap-3 bg-rose-50 border border-rose-200 text-rose-800 p-4 rounded-2xl shadow-sm transition-all">
        <div class="w-8 h-8 bg-rose-500 rounded-xl flex items-center justify-center flex-shrink-0 text-white font-bold shadow-sm mt-0.5">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </div>
        <div class="flex-1">
            <strong class="block text-sm font-bold text-rose-900">Terjadi Kesalahan Input:</strong>
            <ul class="list-disc ml-4 text-xs text-rose-700 mt-1 space-y-0.5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
    @endif

    {{-- Main Grid Content --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- CARD 1: IDENTITAS APLIKASI --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
            <div>
                {{-- Card Header --}}
                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-blue-50 text-blue-600 rounded-xl flex items-center justify-center font-bold">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-bold text-slate-800 text-base leading-tight">Identitas Aplikasi</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Konfigurasi nama sekolah, branding, dan logo utama aplikasi</p>
                        </div>
                    </div>
                    <span class="text-xs font-semibold px-2.5 py-1 bg-blue-50 text-blue-700 rounded-lg">Branding</span>
                </div>

                {{-- Form Identitas --}}
                <form id="form-identitas" action="{{ route('admin.pengaturan.store') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6"
                      x-data="{
                          logoPreview: '{{ !empty($settings['app_logo']) ? (str_starts_with($settings['app_logo'], 'data:image') ? $settings['app_logo'] : asset('uploads/logo/' . $settings['app_logo'])) : '' }}',
                          handleLogoPreview(e) {
                              const file = e.target.files[0];
                              if (file) {
                                  const reader = new FileReader();
                                  reader.onload = (event) => {
                                      this.logoPreview = event.target.result;
                                  };
                                  reader.readAsDataURL(file);
                              }
                          }
                      }">
                    @csrf

                    {{-- Logo Upload Section with Live Preview --}}
                    <div>
                        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Logo Sekolah / Institusi</label>
                        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-5 p-4 bg-slate-50 border border-slate-200 rounded-2xl">
                            <div class="flex-shrink-0">
                                <template x-if="logoPreview">
                                    <div class="w-20 h-20 rounded-xl overflow-hidden bg-white shadow-sm border border-slate-200 flex items-center justify-center p-1.5 relative group">
                                        <img :src="logoPreview" class="w-full h-full object-contain" alt="Logo Preview">
                                    </div>
                                </template>
                                <template x-if="!logoPreview">
                                    <div class="w-20 h-20 bg-blue-100 text-blue-600 rounded-xl flex flex-col items-center justify-center border border-blue-200 shadow-sm text-center p-2">
                                        <svg class="w-8 h-8 opacity-80" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="text-[10px] font-semibold mt-1">No Logo</span>
                                    </div>
                                </template>
                            </div>
                            <div class="flex-1 min-w-0">
                                <input type="file" name="app_logo" accept=".png,.jpg,.jpeg" @change="handleLogoPreview($event)" class="w-full text-xs text-slate-600 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-bold file:bg-blue-600 file:text-white hover:file:bg-blue-700 file:cursor-pointer border border-slate-200 rounded-xl p-1 bg-white focus:outline-none mb-2 transition-all">
                                <p class="text-[11px] text-slate-500 leading-relaxed">
                                    Format gambar yang didukung: <span class="font-semibold text-slate-700">PNG, JPG, JPEG</span>. Disarankan rasio 1:1 transparan dengan ukuran maksimal 2MB.
                                </p>
                            </div>
                        </div>
                    </div>

                    {{-- Nama & Subnama --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Judul / Nama Singkat <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" 
                                   name="app_name" 
                                   value="{{ old('app_name', $settings['app_name'] ?? '') }}" 
                                   required 
                                   placeholder="Contoh: SPENSA" 
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium">
                            <p class="text-[11px] text-slate-400 mt-1.5">Ditampilkan pada judul header navbar dan tab browser.</p>
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Sub Judul / Keterangan
                            </label>
                            <input type="text" 
                                   name="app_subname" 
                                   value="{{ old('app_subname', $settings['app_subname'] ?? '') }}" 
                                   placeholder="Contoh: Presensi Digital" 
                                   class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all font-medium">
                            <p class="text-[11px] text-slate-400 mt-1.5">Ditampilkan di bawah judul utama pada bilah navigasi.</p>
                        </div>
                    </div>
                </form>
            </div>

            {{-- Card Footer --}}
            <div class="px-6 py-4 bg-slate-50/80 border-t border-slate-100 flex items-center justify-end rounded-b-2xl">
                <button type="submit" form="form-identitas" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl shadow-md shadow-blue-500/20 hover:shadow-lg transition-all active:scale-[0.98]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                    </svg>
                    Simpan Pengaturan
                </button>
            </div>
        </div>

        {{-- RIGHT COLUMN: KKM & KOMENTAR --}}
        <div class="space-y-6">

            {{-- CARD 2: PENGATURAN AKADEMIK --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
                <div>
                    {{-- Card Header --}}
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-amber-50 text-amber-600 rounded-xl flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5zm0 0v6"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 text-base leading-tight">Pengaturan Akademik</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Standar ketuntasan minimal nilai siswa</p>
                            </div>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 bg-amber-50 text-amber-700 rounded-lg">KKM</span>
                    </div>

                    {{-- Form KKM --}}
                    <form id="form-kkm" action="{{ route('admin.setting-kkm') }}" method="POST" class="p-6 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Nilai KKM Acuan (0 - 100) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" 
                                       name="kkm_nilai" 
                                       min="0" 
                                       max="100" 
                                       step="any"
                                       value="{{ old('kkm_nilai', $settings['kkm_nilai'] ?? 75) }}" 
                                       required 
                                       placeholder="75" 
                                       class="w-full pl-4 pr-12 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all font-semibold">
                                <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-xs font-bold text-slate-400">
                                    PTS/PAS
                                </div>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1.5 leading-relaxed">
                                Nilai ini digunakan sebagai batas nilai tuntas siswa pada rekap nilai dan hasil ujian harian.
                            </p>
                        </div>
                    </form>
                </div>

                {{-- Card Footer --}}
                <div class="px-6 py-4 bg-slate-50/80 border-t border-slate-100 flex items-center justify-end rounded-b-2xl">
                    <button type="submit" form="form-kkm" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold rounded-xl shadow-md shadow-amber-500/20 hover:shadow-lg transition-all active:scale-[0.98]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Simpan KKM
                    </button>
                </div>
            </div>

            {{-- CARD 3: PENGATURAN KOMENTAR --}}
            <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden flex flex-col justify-between">
                <div>
                    {{-- Card Header --}}
                    <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-violet-50 text-violet-600 rounded-xl flex items-center justify-center font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800 text-base leading-tight">Pengaturan Komentar</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Limit testimoni siswa di homepage</p>
                            </div>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 bg-violet-50 text-violet-700 rounded-lg">Homepage</span>
                    </div>

                    {{-- Form Komentar --}}
                    <form id="form-komentar" action="{{ route('admin.setting-komentar') }}" method="POST" class="p-6 space-y-4">
                        @csrf
                        <div>
                            <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                                Limit Komentar Homepage (1 - 500) <span class="text-rose-500">*</span>
                            </label>
                            <div class="relative">
                                <input type="number" 
                                       name="komentar_homepage_limit" 
                                       min="1" 
                                       max="500" 
                                       value="{{ old('komentar_homepage_limit', $settings['komentar_homepage_limit'] ?? 50) }}" 
                                       required 
                                       placeholder="50" 
                                       class="w-full pl-4 pr-16 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 text-sm focus:bg-white focus:outline-none focus:ring-2 focus:ring-violet-500/20 focus:border-violet-500 transition-all font-semibold">
                                <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center pointer-events-none text-xs font-bold text-slate-400">
                                    Item
                                </div>
                            </div>
                            <p class="text-[11px] text-slate-400 mt-1.5 leading-relaxed">
                                Jumlah maksimal testimoni / kesan siswa yang diputar pada slider halaman beranda utama.
                            </p>
                        </div>
                    </form>
                </div>

                {{-- Card Footer --}}
                <div class="px-6 py-4 bg-slate-50/80 border-t border-slate-100 flex items-center justify-end rounded-b-2xl">
                    <button type="submit" form="form-komentar" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-violet-600 hover:bg-violet-700 text-white text-sm font-bold rounded-xl shadow-md shadow-violet-500/20 hover:shadow-lg transition-all active:scale-[0.98]">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Simpan
                    </button>
                </div>
            </div>

        </div>

    </div>

</div>
@endsection
