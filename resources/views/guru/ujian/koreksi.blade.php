@extends('layouts.app')
@section('title', 'Koreksi Jawaban Essay')
@section('page_title', 'Koreksi Ujian Essay')
@section('content')

@if(session('success'))
<div class="mb-4 bg-emerald-50 text-emerald-800 p-3.5 rounded-xl text-sm border border-emerald-200 shadow-xs flex items-center gap-2">
    <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
    <span class="font-medium">{{ session('success') }}</span>
</div>
@endif

@if($errors->any())
<div class="mb-4 bg-red-50 text-red-800 p-3.5 rounded-xl text-sm border border-red-200 shadow-xs">
    <div class="font-bold mb-1 flex items-center gap-1.5 text-red-700">
        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>
        Peringatan:
    </div>
    <ul class="list-disc list-inside space-y-0.5 text-xs text-red-600">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<!-- Header Informasi Ujian -->
<div class="mb-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-white p-4 sm:p-5 rounded-2xl border border-slate-200 shadow-xs">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <span class="text-xs bg-blue-50 text-blue-700 font-extrabold px-2.5 py-0.5 rounded-md border border-blue-100 uppercase">{{ $ujian->bab }}</span>
            <span class="text-xs text-slate-500 font-semibold">KKM: <span class="text-slate-700 font-bold">{{ $kkm }}</span></span>
            <span class="text-xs text-slate-400">•</span>
            <span class="text-xs text-slate-500 font-semibold">{{ $soalEssay->count() }} Soal Essay</span>
        </div>
        <h2 class="text-lg font-bold text-slate-800">{{ $ujian->judul }}</h2>
        <p class="text-xs text-slate-500 mt-0.5">Penilaian essay dilakukan per-kelas untuk performa sistem yang optimal.</p>
    </div>
    <div class="flex items-center gap-2 flex-shrink-0">
        <a href="{{ route('guru.hasil.show', $ujian->id) }}" class="inline-flex items-center gap-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs py-2 px-3.5 rounded-xl transition-all border border-slate-200/80">
            ← Kembali ke Hasil
        </a>
    </div>
</div>

<!-- Tab Pemilih Rombel / Kelas -->
@if(isset($kelasList) && $kelasList->count() > 0)
<div class="bg-white p-3 rounded-2xl border border-slate-200 shadow-xs mb-4">
    <div class="flex items-center gap-2 overflow-x-auto pb-1 scrollbar-hide">
        <span class="text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap mr-1 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
            Pilih Kelas:
        </span>
        @foreach($kelasList as $k)
        @php
            $stat = $statsPerKelas->get($k->id);
            $totalMhs = $stat ? $stat->total : 0;
            $needGrade = $stat ? $stat->belum_dinilai : 0;
            $isActive = (isset($kelas_id) && $kelas_id == $k->id);
        @endphp
        <a href="{{ route('guru.ujian.koreksi', $ujian->id) }}?kelas_id={{ $k->id }}" 
           class="px-3.5 py-2 rounded-xl text-xs font-bold whitespace-nowrap transition-all flex items-center gap-2 {{ $isActive ? 'bg-blue-600 text-white shadow-sm ring-2 ring-blue-600/30' : 'bg-slate-50 border border-slate-200 text-slate-600 hover:bg-slate-100 hover:text-slate-900' }}">
            <span>Kelas {{ $k->nama_kelas }}</span>
            @if($totalMhs > 0)
                @if($needGrade > 0)
                    <span class="px-1.5 py-0.5 rounded-md text-[10px] font-black {{ $isActive ? 'bg-amber-400 text-amber-950' : 'bg-amber-100 text-amber-800' }}">
                        {{ $needGrade }} perlu dinilai
                    </span>
                @else
                    <span class="px-1.5 py-0.5 rounded-md text-[10px] font-black {{ $isActive ? 'bg-blue-800 text-white' : 'bg-emerald-100 text-emerald-700' }}">
                        ✓ Lengkap
                    </span>
                @endif
            @else
                <span class="text-[10px] font-normal opacity-70">(0 siswa)</span>
            @endif
        </a>
        @endforeach
    </div>
</div>
@endif

@if(empty($kelas_id))
    <!-- TAMPILAN KOSONG / BELUM PILIH KELAS (Menghemat Beban Server & Browser) -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-8 sm:p-12 text-center">
        <div class="w-16 h-16 bg-blue-50 text-blue-600 rounded-2xl flex items-center justify-center mx-auto mb-4 border border-blue-100 shadow-xs">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
            </svg>
        </div>
        <h3 class="text-base sm:text-lg font-bold text-slate-800 mb-1">Silakan Pilih Kelas / Rombel Terlebih Dahulu</h3>
        <p class="text-xs sm:text-sm text-slate-500 max-w-lg mx-auto mb-6 leading-relaxed">
            Untuk menjaga performa aplikasi tetap cepat dan ringan, data jawaban essay dimuat per-kelas. Silakan klik salah satu kelas di bawah ini untuk memulai pemeriksaan.
        </p>

        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 max-w-2xl mx-auto">
            @foreach($kelasList as $k)
            @php
                $stat = $statsPerKelas->get($k->id);
                $totalMhs = $stat ? $stat->total : 0;
                $needGrade = $stat ? $stat->belum_dinilai : 0;
            @endphp
            <a href="{{ route('guru.ujian.koreksi', $ujian->id) }}?kelas_id={{ $k->id }}" 
               class="p-4 rounded-xl border border-slate-200 bg-slate-50/60 hover:bg-blue-50/50 hover:border-blue-300 transition-all text-left group">
                <div class="font-bold text-slate-800 text-sm group-hover:text-blue-700 flex items-center justify-between">
                    <span>Kelas {{ $k->nama_kelas }}</span>
                    <span class="text-slate-400 group-hover:translate-x-0.5 transition-transform text-xs">→</span>
                </div>
                <div class="mt-2 text-xs">
                    @if($totalMhs > 0)
                        <span class="text-slate-500 font-medium">{{ $totalMhs }} peserta</span>
                        @if($needGrade > 0)
                            <span class="block text-amber-600 font-bold mt-0.5 text-[11px]">{{ $needGrade }} belum dikoreksi</span>
                        @else
                            <span class="block text-emerald-600 font-bold mt-0.5 text-[11px]">✓ Selesai dinilai</span>
                        @endif
                    @else
                        <span class="text-slate-400 italic">Belum ada peserta</span>
                    @endif
                </div>
            </a>
            @endforeach
        </div>
    </div>
@else
    <!-- TAMPILAN FORM KOREKSI PER-KELAS -->
    @if($hasilUjians->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-8 text-center">
            <div class="w-12 h-12 bg-slate-100 text-slate-400 rounded-xl flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
            </div>
            <h3 class="font-bold text-slate-700 text-sm">Belum Ada Peserta di Kelas {{ $selectedKelas->nama_kelas ?? '' }}</h3>
            <p class="text-xs text-slate-500 mt-1">Belum ada siswa dari rombel ini yang menyelesaikan ujian.</p>
        </div>
    @else
        <div x-data="{ searchQuery: '' }">
            <form action="{{ route('guru.ujian.koreksi.store', $ujian->id) }}" method="POST">
                @csrf
                <input type="hidden" name="kelas_id" value="{{ $kelas_id }}">

                <div class="bg-white rounded-2xl border border-slate-200 shadow-xs p-4 sm:p-5 mb-6">
                    <!-- Action Bar Atas -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center pb-4 mb-5 border-b border-slate-100 gap-3">
                        <div>
                            <h3 class="font-bold text-slate-800 text-sm sm:text-base flex items-center gap-2">
                                <span>Lembar Jawaban: Kelas {{ $selectedKelas->nama_kelas ?? '' }}</span>
                                <span class="bg-blue-100 text-blue-800 text-xs px-2 py-0.5 rounded-full font-extrabold">{{ $hasilUjians->count() }} Siswa</span>
                            </h3>
                            <span class="text-xs text-slate-500 mt-0.5 block">Nilai akhir otomatis dihitung proporsional antara Pilihan Ganda dan Essay.</span>
                        </div>
                        
                        <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto">
                            <!-- Quick Search Filter Siswa -->
                            <div class="relative flex-1 sm:w-56">
                                <input type="text" 
                                       x-model="searchQuery" 
                                       placeholder="Cari nama siswa..." 
                                       class="w-full text-xs rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 pl-8 focus:bg-white focus:outline-none focus:border-blue-400 transition-colors">
                                <svg class="w-3.5 h-3.5 text-slate-400 absolute left-2.5 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                            </div>

                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs py-2 px-4 rounded-xl shadow-xs transition-all flex items-center gap-1.5 whitespace-nowrap">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <span>Simpan Nilai Kelas Ini</span>
                            </button>
                        </div>
                    </div>
                    
                    <!-- List Siswa -->
                    <div class="space-y-6">
                        @foreach($hasilUjians as $index => $hasil)
                        @php 
                            $siswaJawaban = $jawabanEssay->get($hasil->siswa_id) ?? collect();
                            $studentName = $hasil->siswa->user->name ?? 'Siswa';
                        @endphp
                        <div x-show="!searchQuery || '{{ strtolower(addslashes($studentName)) }}'.includes(searchQuery.toLowerCase())" 
                             class="p-4 sm:p-5 rounded-xl border border-slate-200 hover:border-blue-300 transition-colors bg-slate-50/30">
                            
                            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center pb-3 border-b border-slate-200/80 mb-4 gap-3">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-bold text-slate-800 text-sm sm:text-base">{{ $studentName }}</h4>
                                        @if($hasil->status === 'dinilai')
                                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200">✓ Sudah Dinilai</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded text-[10px] font-black bg-amber-100 text-amber-800 border border-amber-200">⏳ Belum Dinilai</span>
                                        @endif
                                    </div>
                                    <div class="text-xs text-slate-500 mt-0.5 flex items-center gap-2">
                                        <span>NISN: <b>{{ $hasil->siswa->nisn ?? '-' }}</b></span>
                                        <span>•</span>
                                        <span>Nilai PG: <b class="text-slate-700">{{ $hasil->nilai_pg }}</b></span>
                                        @if($hasil->nilai_akhir !== null)
                                        <span>•</span>
                                        <span>Nilai Akhir Saat Ini: <b class="text-blue-700">{{ $hasil->nilai_akhir }}</b></span>
                                        @endif
                                    </div>
                                </div>
                                
                                <!-- Input Nilai Essay -->
                                <div class="flex items-center gap-2 bg-white px-3 py-1.5 rounded-xl border border-slate-200 shadow-2xs">
                                    <label class="text-xs font-bold text-slate-700 whitespace-nowrap">Nilai Essay (0-100):</label>
                                    <input type="number" 
                                           name="nilai_essay[{{ $hasil->siswa_id }}]" 
                                           min="0" 
                                           max="100" 
                                           step="0.5" 
                                           required 
                                           value="{{ old('nilai_essay.'.$hasil->siswa_id, $hasil->nilai_essay) }}" 
                                           class="w-20 text-center font-extrabold text-blue-700 bg-slate-50 border border-slate-300 rounded-lg py-1 px-2 text-sm focus:bg-white focus:ring-2 focus:ring-blue-500 focus:outline-none transition-all">
                                </div>
                            </div>

                            <!-- List Jawaban Essay Siswa -->
                            <div class="space-y-4 pl-1 sm:pl-2">
                                @foreach($soalEssay as $soalIdx => $soal)
                                @php
                                    $jwb = $siswaJawaban->where('soal_id', $soal->id)->first();
                                    $hasAnswer = $jwb && trim((string)$jwb->jawaban) !== '';
                                    $aiData = $jwb?->aiKoreksi;
                                @endphp
                                <div class="text-xs border-l-2 border-indigo-300 pl-3.5 py-1" 
                                     @if($hasAnswer)
                                     x-data="aiKoreksiItem({
                                         jawabanId: {{ $jwb->id }},
                                         siswaId: {{ $hasil->siswa_id }},
                                         ai: {{ $aiData ? json_encode([
                                             'id' => $aiData->id,
                                             'score' => $aiData->score,
                                             'max_score' => $aiData->max_score,
                                             'score_percentage' => (float)$aiData->score_percentage,
                                             'reason' => $aiData->reason,
                                             'strengths' => $aiData->strengths ?? [],
                                             'weaknesses' => $aiData->weaknesses ?? [],
                                             'feedback' => $aiData->feedback,
                                             'confidence' => (float)$aiData->confidence,
                                             'confidence_percent' => round(((float)$aiData->confidence) * 100),
                                             'status' => $aiData->status,
                                             'model' => $aiData->model,
                                             'error_message' => $aiData->error_message
                                         ]) : 'null' }}
                                     })"
                                     @endif>
                                    
                                    <div class="flex items-center justify-between gap-2 mb-1.5">
                                        <span class="font-bold text-slate-700">Pertanyaan {{ $soalIdx + 1 }} <span class="text-slate-400 font-normal">(Bobot: {{ $soal->bobot }})</span>:</span>
                                        @if($hasAnswer)
                                        <button type="button" 
                                                @click="runAiGrading()" 
                                                :disabled="loading"
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-extrabold shadow-2xs border transition-all cursor-pointer"
                                                :class="loading ? 'bg-slate-100 text-slate-400 border-slate-200 cursor-not-allowed' : (ai && ai.status !== 'failed' ? 'bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border-indigo-200' : 'bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white border-transparent')">
                                            <span x-show="!loading" x-text="ai && ai.status !== 'failed' ? '🔄 Koreksi Ulang AI' : '🤖 Koreksi dengan AI'"></span>
                                            <span x-show="loading" class="inline-flex items-center gap-1">
                                                <svg class="animate-spin h-3 w-3 text-slate-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                                Menganalisis...
                                            </span>
                                        </button>
                                        @endif
                                    </div>
                                    <div class="text-slate-800 font-semibold mb-2 leading-relaxed bg-white/60 p-2.5 rounded-lg border border-slate-100">{!! nl2br(e($soal->pertanyaan)) !!}</div>
                                    
                                    <div class="font-bold text-slate-500 mb-1 text-[11px] uppercase tracking-wider">Jawaban Siswa:</div>
                                    @if($hasAnswer)
                                    <div class="text-slate-800 bg-white p-3 rounded-lg border border-slate-200 font-mono whitespace-pre-wrap leading-relaxed shadow-2xs">{{ $jwb->jawaban }}</div>

                                    <!-- Panel Hasil AI Koreksi -->
                                    <div x-cloak x-show="errorMsg" class="mt-2 p-2.5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs flex items-start justify-between gap-2">
                                        <div class="flex items-center gap-1.5">
                                            <span class="font-bold">⚠️</span>
                                            <span x-text="errorMsg"></span>
                                        </div>
                                        <button type="button" @click="errorMsg = ''" class="text-red-400 hover:text-red-600 font-bold">✕</button>
                                    </div>

                                    <div x-cloak x-show="ai && ai.status !== 'failed'" class="mt-2.5 p-3.5 rounded-xl border border-indigo-200 bg-gradient-to-br from-indigo-50/40 via-white to-blue-50/30 space-y-2.5 shadow-2xs">
                                        <div class="flex flex-wrap items-center justify-between gap-2 pb-2 border-b border-indigo-100">
                                            <div class="flex items-center gap-2">
                                                <span class="px-2 py-0.5 rounded-md bg-indigo-600 text-white font-black text-[10px] uppercase tracking-wider">🤖 Koreksi AI</span>
                                                <span class="text-[10px] text-slate-400 font-mono font-medium" x-text="'Model: ' + (ai ? ai.model : '')"></span>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <div class="text-[11px] font-bold text-slate-600">
                                                    Confidence: <span class="text-indigo-700 font-extrabold" x-text="(ai ? ai.confidence_percent : 0) + '%'"></span>
                                                </div>
                                                <template x-if="ai && ai.status === 'approved'">
                                                    <span class="px-2 py-0.5 bg-emerald-100 text-emerald-800 border border-emerald-300 font-extrabold rounded-md text-[10px] flex items-center gap-1">
                                                        ✓ Skor AI Disetujui
                                                    </span>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- Baris Skor & Equivalent -->
                                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                                            <div class="bg-white p-2 rounded-lg border border-slate-200 text-center">
                                                <span class="text-[10px] font-bold text-slate-400 uppercase block">Skor Rubrik AI</span>
                                                <span class="text-base font-black text-indigo-700" x-text="(ai ? ai.score : 0) + ' / 20'"></span>
                                            </div>
                                            <div class="bg-white p-2 rounded-lg border border-indigo-200 text-center">
                                                <span class="text-[10px] font-bold text-indigo-500 uppercase block">Nilai Setara (Skala 100)</span>
                                                <span class="text-base font-black text-blue-700" x-text="(ai ? ai.score_percentage : 0) + ' / 100'"></span>
                                            </div>
                                            <div class="col-span-2 sm:col-span-1 flex items-center justify-center">
                                                <template x-if="ai && ai.status !== 'approved'">
                                                    <button type="button" 
                                                            @click="acceptAiScore()" 
                                                            :disabled="accepting"
                                                            class="w-full h-full min-h-[38px] px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-xs shadow-xs border border-emerald-700 transition-all flex items-center justify-center gap-1 cursor-pointer">
                                                        <span x-show="!accepting">✓ Terima Skor AI</span>
                                                        <span x-show="accepting">Menerapkan...</span>
                                                    </button>
                                                </template>
                                            </div>
                                        </div>

                                        <!-- Alasan & Feedback -->
                                        <div class="space-y-1.5 text-xs">
                                            <div>
                                                <span class="font-bold text-slate-600">Alasan Penilaian:</span>
                                                <p class="text-slate-700 mt-0.5 leading-relaxed bg-white/80 p-2 rounded border border-slate-100" x-text="ai ? ai.reason : ''"></p>
                                            </div>

                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 pt-1" x-show="ai && ((ai.strengths && ai.strengths.length > 0) || (ai.weaknesses && ai.weaknesses.length > 0))">
                                                <div class="bg-emerald-50/40 p-2 rounded-lg border border-emerald-100" x-show="ai && ai.strengths && ai.strengths.length > 0">
                                                    <span class="font-bold text-emerald-800 block mb-1">👍 Kelebihan:</span>
                                                    <ul class="list-disc list-inside space-y-0.5 text-slate-700">
                                                        <template x-for="st in (ai ? ai.strengths : [])">
                                                            <li x-text="st"></li>
                                                        </template>
                                                    </ul>
                                                </div>
                                                <div class="bg-amber-50/40 p-2 rounded-lg border border-amber-100" x-show="ai && ai.weaknesses && ai.weaknesses.length > 0">
                                                    <span class="font-bold text-amber-800 block mb-1">🔍 Kekurangan:</span>
                                                    <ul class="list-disc list-inside space-y-0.5 text-slate-700">
                                                        <template x-for="wk in (ai ? ai.weaknesses : [])">
                                                            <li x-text="wk"></li>
                                                        </template>
                                                    </ul>
                                                </div>
                                            </div>

                                            <div x-show="ai && ai.feedback">
                                                <span class="font-bold text-slate-600">Feedback Untuk Siswa:</span>
                                                <p class="text-slate-700 mt-0.5 italic bg-white/80 p-2 rounded border border-slate-100" x-text="ai ? ai.feedback : ''"></p>
                                            </div>
                                        </div>
                                    </div>

                                    @else
                                    <div class="text-red-500 italic p-2 bg-red-50/50 rounded-lg border border-red-100 text-xs">Siswa tidak menjawab pertanyaan ini.</div>
                                    @endif
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- Action Bar Bawah -->
                    <div class="flex flex-col sm:flex-row justify-between items-center gap-3 border-t border-slate-200 pt-4 mt-6">
                        <span class="text-xs text-slate-500">Pastikan semua nilai siswa telah terisi sebelum menyimpan.</span>
                        <button type="submit" class="w-full sm:w-auto bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm px-6 py-2.5 rounded-xl shadow-xs transition-all flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            <span>Simpan Nilai Essay Kelas {{ $selectedKelas->nama_kelas ?? '' }}</span>
                        </button>
                    </div>

                </div>
            </form>
        </div>
    @endif
@endif

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('aiKoreksiItem', (config) => ({
        jawabanId: config.jawabanId,
        siswaId: config.siswaId,
        ai: config.ai,
        loading: false,
        accepting: false,
        errorMsg: '',

        async runAiGrading() {
            if (this.loading) return;
            this.loading = true;
            this.errorMsg = '';

            try {
                const res = await fetch(`/app/ujian/jawaban/${this.jawabanId}/koreksi-ai`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const json = await res.json();
                if (res.ok && json.status === 'success') {
                    this.ai = json.data;
                } else {
                    this.errorMsg = json.message || 'Koreksi AI gagal diproses.';
                }
            } catch (err) {
                this.errorMsg = 'Gagal menghubungi server. Silakan periksa koneksi atau coba beberapa saat lagi.';
            } finally {
                this.loading = false;
            }
        },

        async acceptAiScore() {
            if (!this.ai || !this.ai.id || this.accepting) return;
            this.accepting = true;
            this.errorMsg = '';

            try {
                const res = await fetch(`/app/ujian/koreksi-ai/${this.ai.id}/accept`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });

                const json = await res.json();
                if (res.ok && json.status === 'success') {
                    this.ai.status = 'approved';
                    // Update input nilai_essay siswa pada formulir
                    const inputEl = document.querySelector(`input[name="nilai_essay[${this.siswaId}]"]`);
                    if (inputEl) {
                        inputEl.value = json.data.accepted_score;
                        inputEl.classList.add('bg-emerald-50', 'text-emerald-800');
                        setTimeout(() => {
                            inputEl.classList.remove('bg-emerald-50', 'text-emerald-800');
                        }, 1500);
                    }
                } else {
                    this.errorMsg = json.message || 'Gagal menerima skor AI.';
                }
            } catch (err) {
                this.errorMsg = 'Terjadi kesalahan jaringan saat menerima skor AI.';
            } finally {
                this.accepting = false;
            }
        }
    }));
});
</script>

@endsection
