@extends('layouts.app')
@section('title', 'Kelola Materi Belajar')
@section('page_title', 'Materi Belajar (E-Learning)')
@section('content')

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
    <!-- Form Upload Materi -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded border border-slate-200 shadow-sm p-4 sticky top-6">
            <h3 class="font-bold text-slate-800 mb-4 border-b border-slate-100 pb-2">Unggah Materi Baru</h3>
            <form action="{{ url('/app/materi') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Judul Materi / Tugas</label>
                    <input type="text" name="judul" required class="input-compact w-full bg-slate-50" placeholder="Contoh: Modul 1 - Aljabar">
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Tautan (Link Opsional)</label>
                    <input type="url" name="link" class="input-compact w-full bg-slate-50" placeholder="https://youtube.com/... atau Drive">
                    <p class="text-[10px] text-slate-400 mt-1">Siswa akan diarahkan ke link ini jika diisi.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Dokumen Materi (Opsional)</label>
                    <input type="file" name="file_materi" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.zip,.rar,.jpg,.jpeg,.png" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded p-1 bg-slate-50">
                    <p class="text-[10px] text-slate-400 mt-1">Maks 5MB. Format: PDF, Word, Excel, PPT, ZIP, RAR, Gambar.</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Foto / Sampul (Opsional)</label>
                    <input type="file" name="foto" accept="image/*" class="w-full text-xs text-slate-500 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded p-1 bg-slate-50">
                    <p class="text-[10px] text-slate-400 mt-1">Maks 2MB. Disarankan rasio landscape (16:9).</p>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-slate-600 mb-1">Deskripsi Singkat (Opsional)</label>
                    <textarea name="deskripsi" rows="3" class="input-compact w-full bg-slate-50" placeholder="Tuliskan petunjuk atau keterangan singkat..."></textarea>
                </div>
                
                <button type="submit" class="btn-compact w-full bg-blue-600 hover:bg-blue-700 text-white shadow-sm flex justify-center items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"></path></svg>
                    Unggah Materi
                </button>
            </form>
        </div>
    </div>

    <!-- Daftar Materi -->
    <div class="lg:col-span-2">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <h3 class="font-bold text-slate-800">Materi yang Telah Diunggah</h3>
            <form action="{{ url('/app/materi') }}" method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul materi..." class="input-compact bg-white text-xs w-48 sm:w-64 border-slate-300 rounded shadow-sm">
                <button type="submit" class="btn-compact bg-slate-800 text-white text-xs px-3 py-1.5 rounded hover:bg-slate-700">Cari</button>
                @if(request('search'))
                    <a href="{{ url('/app/materi') }}" class="btn-compact bg-slate-200 text-slate-700 text-xs px-2.5 py-1.5 rounded hover:bg-slate-300 flex items-center">Reset</a>
                @endif
            </form>
        </div>
        
        @if($materis->isEmpty())
        <div class="bg-white rounded border border-dashed border-slate-300 p-8 text-center shadow-sm">
            <svg class="w-12 h-12 text-slate-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            <p class="text-slate-500 font-medium">Belum ada materi belajar.</p>
            <p class="text-sm text-slate-400 mt-1">Materi yang Anda unggah akan muncul di sini dan dapat diakses oleh semua siswa.</p>
        </div>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($materis as $materi)
            <div class="bg-white rounded border border-slate-200 shadow-sm overflow-hidden flex flex-col group">
                @if($materi->foto)
                    <div class="h-40 w-full overflow-hidden bg-slate-100">
                        <img src="{{ \App\Services\FileStorageService::url($materi->foto, 'materi') }}" alt="{{ $materi->judul }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                    </div>
                @else
                    <div class="h-40 w-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center">
                        <svg class="w-12 h-12 text-white/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"></path></svg>
                    </div>
                @endif
                
                <div class="p-4 flex flex-col flex-grow">
                    <div class="flex justify-between items-start mb-2">
                        <h4 class="font-bold text-slate-800 line-clamp-2 leading-tight">{{ $materi->judul }}</h4>
                        <!-- Hapus Button -->
                        <form action="{{ url('/app/materi', $materi->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus materi ini secara permanen?');" class="ml-2">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-slate-400 hover:text-red-500 transition-colors p-1" title="Hapus Materi">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </form>
                    </div>
                    
                    <p class="text-xs text-slate-500 mb-3 flex-grow line-clamp-3">
                        {{ $materi->deskripsi ?: 'Tidak ada deskripsi.' }}
                    </p>
                    
                    <div class="mt-auto flex flex-col gap-2 pt-3 border-t border-slate-100">
                        <span class="text-[10px] font-medium text-slate-400 flex items-center mb-1">
                            <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            {{ $materi->created_at->diffForHumans() }}
                        </span>
                        
                        <div class="flex items-center justify-between">
                            @if($materi->file_materi)
                            <a href="{{ asset('uploads/materi/' . $materi->file_materi) }}" target="_blank" class="text-xs font-bold text-green-600 hover:text-green-800 flex items-center bg-green-50 px-2 py-1 rounded">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                Unduh File
                            </a>
                            @else
                            <span></span>
                            @endif

                            @if($materi->link)
                            <a href="{{ $materi->link }}" target="_blank" class="text-xs font-bold text-blue-600 hover:text-blue-800 flex items-center bg-blue-50 px-2 py-1 rounded">
                                Buka Tautan
                                <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        <div class="mt-6">
            {{ $materis->links() }}
        </div>
        @endif
    </div>
</div>

@endsection
