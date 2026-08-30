@extends('layouts.app')
@section('title', 'Kelola Artikel Web')
@section('page_title', 'Kelola Artikel & Berita Web')
@section('content')

<div x-data="{ editModalOpen: false, editId: null, editJudul: '', editKonten: '', editActionUrl: '' }">

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
        📰
    </div>
    <div class="relative flex-1 min-w-0">
        <h2 class="text-white font-bold text-base sm:text-lg leading-tight">Publikasi Artikel & Berita Sekolah</h2>
        <p class="text-blue-200 text-xs sm:text-sm mt-1 leading-relaxed">
            Kelola konten artikel edukasi, pengumuman, dan kabar terbaru yang ditampilkan pada portal informasi website SPENSA.
        </p>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Form Upload Artikel -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 sticky top-6">
            <h3 class="font-bold text-slate-800 mb-4 border-b border-slate-100 pb-3 flex items-center gap-2 text-sm">
                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Tulis Artikel Baru
            </h3>
            <form action="{{ url('/app/admin/artikel') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Judul Artikel</label>
                    <input type="text" name="judul" required class="input-compact w-full bg-slate-50 min-h-[40px] rounded-xl" placeholder="Ketik judul artikel...">
                </div>
                
                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Isi / Konten Artikel</label>
                    <textarea name="konten" required rows="6" class="input-compact w-full bg-slate-50 rounded-xl" placeholder="Tuliskan isi atau paragraf artikel Anda..."></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Foto Sampul / Gambar</label>
                    <input type="file" name="gambar[]" multiple accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl p-1 bg-slate-50">
                    <p class="text-[11px] text-slate-400 mt-1">Bisa pilih lebih dari satu gambar. Format JPG/PNG (Maks 5MB).</p>
                </div>
                
                <button type="submit" class="btn-compact w-full bg-blue-600 hover:bg-blue-700 text-white shadow-sm font-bold text-xs min-h-[42px] rounded-xl transition-all flex justify-center items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Publikasikan Artikel
                </button>
            </form>
        </div>
    </div>

    <!-- Daftar Artikel -->
    <div class="lg:col-span-2">
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 sm:p-5 border-b border-slate-100 bg-slate-50/70 flex justify-between items-center">
                <div>
                    <h3 class="font-bold text-slate-800 text-sm">Daftar Artikel Terpublikasi</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Artikel yang sedang tayang di web sekolah.</p>
                </div>
                <span class="text-xs bg-blue-50 text-blue-700 border border-blue-200 px-3 py-1 rounded-full font-bold">{{ $artikels->total() }} Artikel</span>
            </div>
            
            <div class="divide-y divide-slate-100">
                @forelse($artikels as $artikel)
                <div class="p-4 sm:p-5 hover:bg-slate-50/80 transition-colors flex flex-col sm:flex-row gap-4 justify-between items-start">
                    <div class="flex gap-3.5 flex-1 min-w-0">
                        @php
                            $coverImg = null;
                            if (!empty($artikel->gambar) && is_array($artikel->gambar) && count($artikel->gambar) > 0) {
                                $coverImg = \App\Services\FileStorageService::url($artikel->gambar[0], 'artikels');
                            }
                        @endphp

                        @if($coverImg)
                        <img src="{{ $coverImg }}" alt="Cover" class="w-20 h-20 rounded-xl object-cover border border-slate-200 flex-shrink-0">
                        @else
                        <div class="w-20 h-20 bg-slate-100 border border-slate-200 rounded-xl flex items-center justify-center flex-shrink-0 text-slate-400">
                            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        @endif

                        <div class="min-w-0 flex-1">
                            <h4 class="font-bold text-slate-800 text-sm leading-snug line-clamp-1">{{ $artikel->judul }}</h4>
                            <p class="text-xs text-slate-500 mt-1 line-clamp-2 leading-relaxed">{{ Str::limit(strip_tags($artikel->konten), 140) }}</p>
                            <div class="flex items-center gap-3 mt-2 text-[11px] text-slate-400 font-medium">
                                <span>📅 {{ \Carbon\Carbon::parse($artikel->created_at)->translatedFormat('d M Y, H:i') }}</span>
                                @if(!empty($artikel->gambar) && is_array($artikel->gambar))
                                <span>📷 {{ count($artikel->gambar) }} Gambar</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-1.5 flex-shrink-0 self-end sm:self-center">
                        <button type="button" 
                                @click="editModalOpen = true; editId = {{ $artikel->id }}; editJudul = {{ json_encode($artikel->judul) }}; editKonten = {{ json_encode($artikel->konten) }}; editActionUrl = '{{ url('/app/admin/artikel/' . $artikel->id) }}'" 
                                class="p-2 bg-blue-50 text-blue-600 hover:bg-blue-100 rounded-xl transition-colors font-semibold text-xs inline-flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            <span>Edit</span>
                        </button>
                        
                        <form action="{{ url('/app/admin/artikel/' . $artikel->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus artikel ini?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-2 bg-red-50 text-red-600 hover:bg-red-100 rounded-xl transition-colors" title="Hapus Artikel">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    </div>
                </div>
                @empty
                <div class="p-12 text-center text-slate-400 italic">
                    Belum ada artikel yang dipublikasikan.
                </div>
                @endforelse
            </div>
            
            <div class="p-4 border-t border-slate-100 bg-slate-50">
                {{ $artikels->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Edit Artikel -->
<div x-show="editModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
    <div class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-2xl relative" @click.away="editModalOpen = false">
        <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-3">
            <h3 class="font-bold text-slate-800 text-sm">Edit Artikel</h3>
            <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 text-lg font-bold">&times;</button>
        </div>
        
        <form :action="editActionUrl" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')
            
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Judul Artikel</label>
                <input type="text" name="judul" x-model="editJudul" required class="input-compact w-full bg-slate-50 min-h-[40px] rounded-xl">
            </div>
            
            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Isi / Konten Artikel</label>
                <textarea name="konten" x-model="editKonten" required rows="6" class="input-compact w-full bg-slate-50 rounded-xl"></textarea>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 uppercase tracking-wider mb-1.5">Ganti Gambar (Opsional)</label>
                <input type="file" name="gambar[]" multiple accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-xl p-1 bg-slate-50">
                <p class="text-[11px] text-slate-400 mt-1">Kosongkan jika tidak ingin mengubah foto yang sudah ada.</p>
            </div>
            
            <div class="flex justify-end gap-2 pt-3 border-t border-slate-100">
                <button type="button" @click="editModalOpen = false" class="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-xl">Batal</button>
                <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

</div>
@endsection
