@extends('layouts.app')
@section('title', 'Koreksi Jawaban Essay')
@section('page_title', 'Koreksi Ujian Essay')
@section('content')

@if(session('success'))
<div class="mb-4 bg-green-100 text-green-800 p-3 rounded text-sm border border-green-200 shadow-sm">
    {{ session('success') }}
</div>
@endif

@if($errors->any())
<div class="mb-4 bg-red-100 text-red-800 p-3 rounded text-sm border border-red-200 shadow-sm">
    @foreach ($errors->all() as $error)
        <p>{{ $error }}</p>
    @endforeach
</div>
@endif

<div class="mb-4 flex items-center justify-between">
    <div>
        <h2 class="text-lg font-bold text-slate-800">{{ $ujian->judul }}</h2>
        <p class="text-xs text-slate-500">Materi/Bab: {{ $ujian->bab }} | Masukkan nilai total essay (0-100) untuk masing-masing siswa di bawah ini.</p>
    </div>
    <a href="{{ route('guru.ujian.index') }}" class="btn-compact bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold text-xs py-1.5 px-3 rounded shadow-sm">Kembali</a>
</div>

<form action="{{ route('guru.ujian.koreksi.store', $ujian->id) }}" method="POST">
    @csrf

    <div class="bg-white rounded border border-slate-200 shadow-sm p-4">
        
        <div class="space-y-6">
            @forelse($hasilUjians as $index => $hasil)
            @php 
                $siswaJawaban = $jawabanEssay->get($hasil->siswa_id) ?? collect();
            @endphp
            <div class="p-4 rounded-lg border border-slate-200 hover:border-blue-300 transition-colors bg-slate-50/20">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-3 border-b border-slate-100 mb-3 gap-2">
                    <div>
                        <h4 class="font-bold text-slate-800 text-sm">{{ $hasil->siswa->user->name ?? '-' }}</h4>
                        <span class="text-xs font-semibold text-slate-500">Kelas: {{ $hasil->siswa->kelas->nama_kelas ?? '-' }} | Nilai PG: <span class="font-bold text-slate-700">{{ $hasil->nilai_pg }}</span></span>
                    </div>
                    
                    <!-- Input Nilai Essay -->
                    <div class="flex items-center gap-2">
                        <label class="text-xs font-bold text-slate-600">Nilai Essay (0-100):</label>
                        <input type="number" name="nilai_essay[{{ $hasil->siswa_id }}]" min="0" max="100" step="0.5" required value="{{ old('nilai_essay.'.$hasil->siswa_id, $hasil->nilai_essay) }}" class="input-compact bg-white border-slate-300 w-24 text-center font-bold text-blue-700">
                    </div>
                </div>

                <!-- List Jawaban Essay Siswa -->
                <div class="space-y-3.5 pl-2 mt-2">
                    @foreach($soalEssay as $soalIdx => $soal)
                    @php
                        $jwb = $siswaJawaban->where('soal_id', $soal->id)->first();
                    @endphp
                    <div class="text-xs border-l-2 border-indigo-200 pl-3">
                        <div class="font-semibold text-slate-500 mb-1">Pertanyaan {{ $soalIdx + 1 }}:</div>
                        <div class="text-slate-800 font-semibold mb-1.5">{!! nl2br(e($soal->pertanyaan)) !!}</div>
                        
                        <div class="font-semibold text-slate-500 mb-0.5">Jawaban Siswa:</div>
                        @if($jwb && trim($jwb->jawaban) !== '')
                        <div class="text-slate-800 bg-white p-2 rounded border border-slate-200 font-mono whitespace-pre-wrap">{!! e($jwb->jawaban) !!}</div>
                        @else
                        <div class="text-red-500 italic p-1">Siswa tidak menjawab pertanyaan ini.</div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @empty
            <div class="text-center py-8 text-slate-500 italic">Belum ada peserta yang selesai mengerjakan ujian ini.</div>
            @endforelse
        </div>

        @if($hasilUjians->count() > 0)
        <div class="flex justify-end gap-2 border-t border-slate-100 pt-4 mt-5">
            <button type="submit" class="btn-compact bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm px-6 py-2 rounded shadow-sm border border-blue-700">
                Simpan Semua Koreksi
            </button>
        </div>
        @endif

    </div>
</form>

@endsection
