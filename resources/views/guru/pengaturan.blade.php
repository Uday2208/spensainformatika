@extends('layouts.app')
@section('title', 'Pengaturan Web')
@section('page_title', 'Pengaturan Web & Aplikasi')
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

<div class="max-w-2xl mx-auto">
    <div class="bg-white p-6 border border-slate-200 rounded shadow-sm">
        <h3 class="font-bold text-slate-800 mb-6 border-b border-slate-100 pb-3 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
            Pengaturan Tampilan Aplikasi
        </h3>
        
        <form action="{{ url('/app/pengaturan') }}" method="POST" enctype="multipart/form-data" class="space-y-5">
            @csrf
            
            <div class="flex items-start gap-4 p-4 bg-slate-50 border border-slate-200 rounded-lg">
                <div class="flex-shrink-0">
                    @if(!empty($settings['app_logo']))
                        <div class="w-20 h-20 rounded-lg overflow-hidden bg-white shadow-sm border border-slate-200 flex items-center justify-center p-1">
                            <img src="{{ asset('uploads/logo/' . $settings['app_logo']) }}" class="w-full h-full object-contain">
                        </div>
                    @else
                        <div class="w-20 h-20 bg-blue-100 text-blue-500 rounded-lg flex items-center justify-center border border-blue-200 shadow-sm">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                    @endif
                </div>
                <div class="flex-1">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">Logo Sekolah / Institusi</label>
                    <input type="file" name="app_logo" accept=".png,.jpg,.jpeg" class="w-full text-sm text-slate-500 file:mr-3 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-xs file:font-bold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 border border-slate-200 rounded-md p-1 bg-white mb-2">
                    <p class="text-xs text-slate-500">Gunakan format gambar berlatar belakang transparan (PNG) dengan rasio 1:1 (kotak). Maksimal 2MB.</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Judul Singkat / Akronim</label>
                <input type="text" name="app_name" value="{{ $settings['app_name'] ?? 'SPENSA' }}" required class="w-full p-2.5 border border-slate-300 rounded focus:ring-blue-500 focus:border-blue-500 bg-slate-50" placeholder="Contoh: SMPN 1">
                <p class="text-xs text-slate-400 mt-1">Muncul besar di sebelah logo pada sidebar.</p>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">Sub Judul / Keterangan</label>
                <input type="text" name="app_subname" value="{{ $settings['app_subname'] ?? 'Presensi Digital' }}" required class="w-full p-2.5 border border-slate-300 rounded focus:ring-blue-500 focus:border-blue-500 bg-slate-50" placeholder="Contoh: Sekolah Berbasis Digital">
                <p class="text-xs text-slate-400 mt-1">Muncul di bawah judul dengan ukuran lebih kecil.</p>
            </div>

            <div class="pt-4 border-t border-slate-100">
                <button type="submit" class="w-full md:w-auto px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded shadow flex items-center justify-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>

@endsection
