@extends('layouts.app')
@section('title', 'Lembar Periksa Jawaban Siswa')
@section('page_title', 'Lembar Periksa Jawaban Siswa')
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

<!-- Top Navigation & Action -->
<div class="mb-6 flex justify-between items-center">
    <a href="{{ route('guru.hasil.show', $ujian->id) }}" class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold px-4 py-2 rounded-xl text-sm transition-all inline-flex items-center gap-1.5">
        ← Kembali ke Rekap Hasil Ujian
    </a>
</div>

<!-- Grid Layout -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    <!-- Left Column: Student & Exam Summary Card -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5 h-fit space-y-4">
        <div class="pb-3 border-b border-slate-100">
            <span class="text-xs bg-blue-50 text-blue-700 border border-blue-200 px-2.5 py-0.5 rounded-md font-bold uppercase">{{ $siswa->kelas->nama_kelas ?? 'Siswa' }}</span>
            <h2 class="text-lg font-black text-slate-800 mt-1">{{ $siswa->user->name ?? '-' }}</h2>
            <p class="text-xs text-slate-400 font-mono">NIS: {{ $siswa->nis }}</p>
        </div>

        <div class="space-y-3 text-sm">
            <div>
                <label class="text-slate-400 text-xs font-semibold block">Paket Ujian</label>
                <span class="font-bold text-slate-800 block">{{ $ujian->judul }}</span>
                <span class="text-xs text-slate-500 font-medium">Materi: {{ $ujian->bab }}</span>
            </div>

            <!-- Form dan Wrapper Nilai -->
            @php
                $bobotPg = $ujian->soalPg()->sum('bobot');
                $bobotEssay = $ujian->soalEssay()->sum('bobot');
                $totalBobot = $bobotPg + $bobotEssay;
            @endphp
            <form action="{{ route('guru.hasil.update-siswa', [$ujian->id, $siswa->id]) }}" method="POST"
                 x-data="{ 
                     nilaiPg: {{ (float)old('nilai_pg', $hasil->nilai_pg) }},
                     nilaiEssay: {{ (float)old('nilai_essay', $hasil->nilai_essay) }}, 
                     nilaiAkhir: {{ (float)old('nilai_akhir', $hasil->nilai_akhir) }},
                     hitungOtomatis() {
                         const nPg = parseFloat(this.nilaiPg) || 0;
                         const bPg = {{ $bobotPg }};
                         const bEssay = {{ $bobotEssay }};
                         const tBobot = {{ $totalBobot }};
                         const nEssay = parseFloat(this.nilaiEssay) || 0;

                         if (tBobot > 0 && bEssay > 0) {
                             const calc = ((nPg * bPg) + (nEssay * bEssay)) / tBobot;
                             this.nilaiAkhir = Math.min(100, Math.max(0, parseFloat(calc.toFixed(1))));
                         } else {
                             this.nilaiAkhir = nPg;
                         }
                     }
                 }">
                @csrf
                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-100 mb-3">
                    <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200 text-center">
                        <label class="text-slate-400 text-[10px] font-bold block uppercase mb-1">Skor PG</label>
                        <input type="number" step="0.1" name="nilai_pg" min="0" max="100" 
                               x-model="nilaiPg" 
                               @input="hitungOtomatis()" 
                               class="w-full bg-white border border-slate-300 rounded-md px-1 py-1 text-base font-black text-center text-slate-800 shadow-inner">
                    </div>
                    <div class="bg-indigo-50/50 p-2.5 rounded-xl border border-indigo-100 text-center">
                        <label class="text-indigo-500 text-[10px] font-bold block uppercase mb-1">Skor Essay</label>
                        <span class="text-base font-black text-indigo-700 block mt-1">{{ number_format($hasil->nilai_essay, 1) }}</span>
                    </div>
                </div>

                <div class="p-3.5 rounded-xl border text-center mb-4 {{ $hasil->nilai_akhir >= $kkm ? 'bg-green-50 border-green-200 text-green-800' : 'bg-red-50 border-red-200 text-red-800' }}">
                    <label class="text-xs font-bold block uppercase opacity-75">Nilai Akhir Ujian</label>
                    <span class="text-3xl font-black block my-0.5" x-text="nilaiAkhir.toFixed(1)"></span>
                    <span class="text-xs font-extrabold px-2.5 py-0.5 rounded-full inline-block {{ $hasil->nilai_akhir >= $kkm ? 'bg-green-200 text-green-900' : 'bg-red-200 text-red-900' }}">
                        {{ $hasil->nilai_akhir >= $kkm ? 'LULUS KKM (' . $kkm . ')' : 'REMEDIAL (KKM ' . $kkm . ')' }}
                    </span>
                </div>

                <!-- Koreksi Section -->
                <div class="pt-3 border-t border-slate-100">
                    <h4 class="font-extrabold text-slate-800 text-xs mb-2">✍️ Koreksi / Override Nilai</h4>
                    <div class="space-y-3">
                        @if($ujian->soalEssay()->count() > 0)
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="block text-[11px] font-bold text-slate-600">Nilai Total Essay (0-100)</label>
                                <span class="text-[10px] text-slate-400 font-bold">Bobot: {{ $bobotEssay }}</span>
                            </div>
                            <input type="number" step="0.1" name="nilai_essay" min="0" max="100" 
                                   x-model="nilaiEssay" 
                                   @input="hitungOtomatis()" 
                                   class="w-full bg-slate-50 border border-slate-300 rounded-xl px-3 py-1.5 text-xs font-bold text-center">
                        </div>
                        @endif

                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <label class="block text-[11px] font-bold text-slate-600">Nilai Akhir (Opsional Override)</label>
                                <button type="button" @click="hitungOtomatis()" class="text-[10px] text-blue-600 hover:underline font-bold">⚡ Hitung Otomatis</button>
                            </div>
                            <input type="number" step="0.1" name="nilai_akhir" min="0" max="100" 
                                   x-model="nilaiAkhir" 
                                   class="w-full bg-slate-50 border border-blue-300 focus:border-blue-500 rounded-xl px-3 py-1.5 text-xs font-black text-center text-blue-800 shadow-inner">
                        </div>

                        <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-extrabold py-2 px-3 rounded-xl text-xs shadow-md transition-all active:scale-[0.98]">
                            💾 Simpan Nilai Siswa
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Log Cheat Violations -->
        @if($logs->count() > 0)
        <div class="bg-red-50/50 rounded-2xl border border-red-200 p-4 space-y-2">
            <h4 class="font-extrabold text-red-800 text-xs flex items-center gap-1.5">
                <span>⚠️</span> Log Deteksi Kecurangan ({{ $logs->count() }} Aktivitas)
            </h4>
            <div class="space-y-1.5 max-h-48 overflow-y-auto pr-1">
                @foreach($logs as $log)
                <div class="text-[11px] bg-white p-2 rounded-lg border border-red-100">
                    <span class="font-mono font-bold text-red-600 block">{{ \Carbon\Carbon::parse($log->created_at)->format('H:i:s') }} - {{ strtoupper($log->event) }}</span>
                    <span class="text-slate-600 font-medium">{{ $log->detail }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Right Column: Full Student Answer Sheet -->
    <div class="lg:col-span-2 space-y-6">

        <!-- Pilihan Ganda Answers -->
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <h3 class="font-extrabold text-slate-800 text-base mb-4 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>1. Lembar Jawaban Pilihan Ganda (PG)</span>
                <span class="text-xs text-slate-400 font-medium">Bobot PG: {{ $ujian->soalPg()->sum('bobot') }}</span>
            </h3>

            <div class="space-y-4">
                @forelse($ujian->soals->where('tipe', 'pg') as $index => $soal)
                @php
                    $j = $jawaban[$soal->id] ?? null;
                    $jawabanSiswa = $j ? strtolower(trim($j->jawaban)) : null;
                    $jawabanBenar = strtolower(trim($soal->jawaban_benar));
                    $isCorrect = $jawabanSiswa && ($jawabanSiswa === $jawabanBenar);
                @endphp
                <div class="p-4 rounded-xl border {{ $isCorrect ? 'border-green-200 bg-green-50/20' : ($jawabanSiswa ? 'border-red-200 bg-red-50/20' : 'border-slate-200 bg-slate-50/30') }}">
                    <div class="flex justify-between items-start mb-2 gap-4">
                        <div class="flex items-center gap-2">
                            <span class="w-6 h-6 bg-slate-200 text-slate-800 rounded-full text-xs font-black flex items-center justify-center">{{ $index + 1 }}</span>
                            <span class="text-xs text-slate-500 font-bold">Bobot: {{ $soal->bobot }}</span>
                        </div>
                        <div>
                            @if($isCorrect)
                            <span class="px-2.5 py-0.5 bg-green-100 text-green-800 text-xs font-black rounded-full border border-green-200 flex items-center gap-1">
                                ✓ BENAR
                            </span>
                            @elseif($jawabanSiswa)
                            <span class="px-2.5 py-0.5 bg-red-100 text-red-800 text-xs font-black rounded-full border border-red-200 flex items-center gap-1">
                                ✗ SALAH
                            </span>
                            @else
                            <span class="px-2.5 py-0.5 bg-slate-200 text-slate-600 text-xs font-bold rounded-full">
                                Tidak Dijawab
                            </span>
                            @endif
                        </div>
                    </div>

                    <div class="text-slate-800 text-sm font-semibold mb-3">{!! nl2br(e($soal->pertanyaan)) !!}</div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-xs">
                        @foreach(['a' => $soal->opsi_a, 'b' => $soal->opsi_b, 'c' => $soal->opsi_c, 'd' => $soal->opsi_d] as $key => $opsiText)
                        @php
                            $isChosen = ($jawabanSiswa === $key);
                            $isKunci = ($jawabanBenar === $key);
                        @endphp
                        <div class="p-2 rounded-lg border transition-all flex items-center justify-between {{ $isKunci ? 'bg-green-100 text-green-900 border-green-300 font-bold' : ($isChosen ? 'bg-red-100 text-red-900 border-red-300 font-bold' : 'bg-white border-slate-200 text-slate-700') }}">
                            <div>
                                <span class="font-extrabold uppercase mr-1">{{ $key }}.</span> {{ $opsiText }}
                            </div>
                            <div>
                                @if($isChosen && $isKunci)
                                <span class="text-[10px] bg-green-600 text-white px-1.5 py-0.5 rounded font-bold">Jawaban Siswa (Benar)</span>
                                @elseif($isChosen)
                                <span class="text-[10px] bg-red-600 text-white px-1.5 py-0.5 rounded font-bold">Jawaban Siswa</span>
                                @elseif($isKunci)
                                <span class="text-[10px] bg-green-200 text-green-900 px-1.5 py-0.5 rounded font-bold">Kunci Benar</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @empty
                <p class="text-xs text-slate-400 italic">Tidak ada soal Pilihan Ganda pada ujian ini.</p>
                @endforelse
            </div>
        </div>

        <!-- Essay Answers -->
        @if($ujian->soalEssay()->count() > 0)
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
            <h3 class="font-extrabold text-slate-800 text-base mb-4 pb-2 border-b border-slate-100 flex items-center justify-between">
                <span>2. Lembar Jawaban Essay / Uraian</span>
                <span class="text-xs text-slate-400 font-medium">Bobot Essay: {{ $ujian->soalEssay()->sum('bobot') }}</span>
            </h3>

            <div class="space-y-4">
                @foreach($ujian->soals->where('tipe', 'essay') as $index => $soal)
                @php
                    $j = $jawaban[$soal->id] ?? null;
                    $jawabanText = $j ? trim((string)$j->jawaban) : '';
                    $hasAnswer = $jawabanText !== '';
                    $aiData = $j?->aiKoreksi;
                @endphp
                <div class="p-4 rounded-xl border border-indigo-200 bg-indigo-50/10 space-y-3"
                     @if($hasAnswer && $j)
                     x-data="aiKoreksiDetailItem({
                         jawabanId: {{ $j->id }},
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
                    <div class="flex flex-wrap justify-between items-center gap-2">
                        <span class="font-extrabold text-xs text-indigo-900">Soal Essay {{ $index + 1 }} (Bobot: {{ $soal->bobot }})</span>
                        @if($hasAnswer && $j)
                        <button type="button" 
                                @click="runAiGrading()" 
                                :disabled="loading"
                                class="inline-flex items-center gap-1 px-3 py-1 rounded-lg text-xs font-extrabold shadow-2xs border transition-all cursor-pointer"
                                :class="loading ? 'bg-slate-100 text-slate-400 border-slate-200 cursor-not-allowed' : (ai && ai.status !== 'failed' ? 'bg-indigo-100 hover:bg-indigo-200 text-indigo-800 border-indigo-300' : 'bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white border-transparent')">
                            <span x-show="!loading" x-text="ai && ai.status !== 'failed' ? '🔄 Koreksi Ulang AI' : '🤖 Koreksi dengan AI'"></span>
                            <span x-show="loading" class="inline-flex items-center gap-1">
                                <svg class="animate-spin h-3 w-3 text-slate-500" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                                Menganalisis...
                            </span>
                        </button>
                        @endif
                    </div>

                    <div class="text-slate-800 text-sm font-semibold leading-relaxed">{!! nl2br(e($soal->pertanyaan)) !!}</div>

                    <div class="bg-white border border-slate-200 rounded-xl p-3.5">
                        <label class="text-[10px] font-bold text-slate-400 uppercase block mb-1">Jawaban Yang Diketik Siswa:</label>
                        @if($hasAnswer)
                        <div class="text-slate-800 text-sm leading-relaxed whitespace-pre-wrap font-medium">{{ $jawabanText }}</div>
                        @else
                        <span class="text-xs text-amber-700 italic font-semibold">Siswa tidak mengetikkan jawaban untuk soal essay ini.</span>
                        @endif
                    </div>

                    @if($hasAnswer && $j)
                    <!-- Panel Hasil AI Koreksi -->
                    <div x-cloak x-show="errorMsg" class="p-2.5 rounded-lg bg-red-50 border border-red-200 text-red-700 text-xs flex items-start justify-between gap-2">
                        <div class="flex items-center gap-1.5">
                            <span class="font-bold">⚠️</span>
                            <span x-text="errorMsg"></span>
                        </div>
                        <button type="button" @click="errorMsg = ''" class="text-red-400 hover:text-red-600 font-bold">✕</button>
                    </div>

                    <div x-cloak x-show="ai && ai.status !== 'failed'" class="p-3.5 rounded-xl border border-indigo-200 bg-gradient-to-br from-indigo-50/40 via-white to-blue-50/30 space-y-2.5 shadow-2xs">
                        <div class="flex flex-wrap items-center justify-between gap-2 pb-2 border-b border-indigo-100">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md bg-indigo-600 text-white font-black text-[10px] uppercase tracking-wider">🤖 Rekomendasi AI</span>
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
                    @endif

                </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>

</div>

<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('aiKoreksiDetailItem', (config) => ({
        jawabanId: config.jawabanId,
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
                    // Update input nilai_essay pada form kiri jika ada
                    const inputEl = document.querySelector('input[name="nilai_essay"]');
                    if (inputEl) {
                        inputEl.value = json.data.accepted_score;
                        inputEl.dispatchEvent(new Event('input'));
                    }
                    setTimeout(() => {
                        window.location.reload();
                    }, 800);
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
