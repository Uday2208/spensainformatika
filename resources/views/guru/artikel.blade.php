@extends('layouts.app')
@section('title', 'Kelola Artikel Web')
@section('page_title', 'Kelola Artikel & Berita')
@section('content')

<div x-data="{ editModalOpen: false, editId: null, editJudul: '', editKonten: '', editActionUrl: '' }">

@if(session('success'))
<div class="mb-4 bg-green-100 text-green-800 p-3 rounded text-sm border border-green-200">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="mb-4 bg-red-100 text-red-800 p-3 rounded text-sm border border-red-200">
    <ul class="list-disc ml-5">
        @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Form Upload Artikel -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded border border-slate-200 shadow-sm p-4 sticky top-6">
            <h3 class="font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Tulis Artikel Baru</h3>
            <form action="{{ url('/app/artikel') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Judul Artikel</label>
                    <input type="text" name="judul" required class="input-compact w-full bg-slate-50" placeholder="Ketik judul di sini...">
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Isi / Konten Artikel</label>
                    <textarea name="konten" required rows="6" class="input-compact w-full bg-slate-50" placeholder="Tulis paragraf artikel Anda..."></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Gambar (Bisa pilih lebih dari satu)</label>
                    <input type="file" name="gambar[]" multiple accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded p-1 bg-slate-50">
                    <p class="text-[10px] text-slate-400 mt-1">Tekan CTRL saat memilih file untuk mengunggah beberapa gambar.</p>
                </div>
                
                <button type="submit" class="btn-compact w-full bg-blue-600 hover:bg-blue-700 text-white shadow-sm flex justify-center items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                    Publikasikan Artikel
                </button>
            </form>
        </div>
    </div>

    <!-- Daftar Artikel -->
    <div class="lg:col-span-2">
        <h3 class="font-bold text-slate-800 mb-4">Artikel Terpublikasi</h3>
        
        @if($artikels->isEmpty())
        <div class="bg-white rounded border border-dashed border-slate-300 p-8 text-center shadow-sm">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"></path></svg>
            <p class="text-slate-500 font-medium">Belum ada artikel yang dipublikasikan.</p>
        </div>
        @else
        <div class="space-y-4">
            @foreach($artikels as $artikel)
            <div class="bg-white rounded border border-slate-200 shadow-sm p-4 flex flex-col sm:flex-row gap-4">
                
                <!-- Preview Gambar -->
                <div class="sm:w-48 flex-shrink-0">
                    @if($artikel->gambar && count($artikel->gambar) > 0)
                        <div class="relative w-full h-32 rounded overflow-hidden bg-slate-100 group">
                            <img src="{{ \App\Services\FileStorageService::url($artikel->gambar[0], 'artikel') }}" alt="Preview" class="w-full h-full object-cover">
                            @if(count($artikel->gambar) > 1)
                                <div class="absolute inset-0 bg-black/40 flex items-center justify-center">
                                    <span class="text-white font-bold text-sm">+{{ count($artikel->gambar) - 1 }} Foto</span>
                                </div>
                            @endif
                        </div>
                    @else
                        <div class="w-full h-32 rounded bg-slate-100 border border-slate-200 flex items-center justify-center">
                            <span class="text-xs text-slate-400">Tanpa Gambar</span>
                        </div>
                    @endif
                </div>
                
                <!-- Konten & Tombol Action -->
                <div class="flex-grow flex flex-col justify-between">
                    <div>
                        <div class="flex justify-between items-start gap-2 mb-1">
                            <h4 class="font-bold text-slate-800 text-lg line-clamp-1">{{ $artikel->judul }}</h4>
                            
                            <!-- Action Buttons: Edit & Hapus -->
                            <div class="flex items-center gap-1.5 flex-shrink-0">
                                <!-- Tombol Edit -->
                                <button @click="editModalOpen = true; editId = {{ $artikel->id }}; editJudul = {{ json_encode($artikel->judul) }}; editKonten = {{ json_encode($artikel->konten) }}; editActionUrl = '{{ url('/app/artikel/' . $artikel->id) }}'" 
                                        class="text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 rounded px-2.5 py-1 text-xs font-bold transition flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    Edit
                                </button>

                                <!-- Tombol Hapus -->
                                <form action="{{ url('/app/artikel', $artikel->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus artikel ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-500 hover:text-red-700 bg-red-50 hover:bg-red-100 rounded px-2.5 py-1 text-xs font-bold transition flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <p class="text-xs text-slate-400 mb-2">Diterbitkan: {{ $artikel->created_at->format('d M Y, H:i') }}</p>
                        
                        <p class="text-sm text-slate-600 line-clamp-3">
                            {{ $artikel->konten }}
                        </p>
                    </div>

                    <div class="mt-3 text-right">
                        <a href="{{ route('artikel.show', $artikel->slug) }}" target="_blank" class="text-xs font-bold text-blue-600 hover:underline">
                            Lihat di Web ↗
                        </a>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-6">
            {{ $artikels->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Edit Artikel -->
<div x-cloak x-show="editModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <div x-show="editModalOpen" x-transition.opacity @click="editModalOpen = false" class="fixed inset-0 bg-black/50 backdrop-blur-sm"></div>
    
    <div x-show="editModalOpen" x-transition.scale.95 class="relative bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6 z-10 overflow-hidden border border-slate-100">
        <div class="flex justify-between items-center pb-3 mb-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-800 text-lg flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                Edit Artikel
            </h3>
            <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 rounded-full p-1 hover:bg-slate-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
            </button>
        </div>

        <form :action="editActionUrl" method="POST" enctype="multipart/form-data" class="space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Judul Artikel</label>
                <input type="text" name="judul" x-model="editJudul" required class="input-compact w-full bg-slate-50" placeholder="Ketik judul artikel...">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Isi / Konten Artikel</label>
                <textarea name="konten" x-model="editKonten" required rows="6" class="input-compact w-full bg-slate-50" placeholder="Tulis paragraf artikel..."></textarea>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1">Ganti Gambar (Opsional)</label>
                <input type="file" name="gambar[]" multiple accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded p-1 bg-slate-50">
                <p class="text-[10px] text-slate-400 mt-1">Kosongkan jika tidak ingin mengganti gambar artikel yang ada.</p>
            </div>

            <div class="flex justify-end gap-2 pt-2">
                <button type="button" @click="editModalOpen = false" class="px-4 py-2 text-xs font-bold text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-lg transition-colors">Batal</button>
                <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-blue-600 hover:bg-blue-700 rounded-lg shadow-sm transition-colors">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

</div>

@endsection
