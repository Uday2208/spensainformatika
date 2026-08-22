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

<div class="mb-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 bg-white p-4 rounded-2xl border border-slate-200 shadow-sm">
    <div>
        <div class="flex items-center gap-2 mb-1">
            <span class="text-xs bg-slate-100 text-slate-600 font-extrabold px-2 py-0.5 rounded-md border border-slate-200 uppercase">{{ $ujian->bab }}</span>
            <span class="text-xs text-slate-400 font-semibold">KKM: {{ $kkm }}</span>
        </div>
        <h2 class="text-lg font-bold text-slate-800">{{ $ujian->judul }}</h2>
        <p class="text-xs text-slate-500 mt-0.5">Masukkan skor nilai essay (0–100) per siswa di bawah ini.</p>
    </div>
    <div class="flex items-center gap-2">
        <a href="{{ route('guru.hasil.show', $ujian->id) }}" class="btn-compact bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs py-2 px-3.5 rounded-xl transition-all">← Kembali ke Hasil</a>
    </div>
</div>

<!-- Tab Pemilih Kelas -->
@if(isset($kelasList) && $kelasList->count() > 0)
<div class="flex items-center gap-2 overflow-x-auto pb-2 mb-4">
    <span class="text-xs font-bold text-slate-400 uppercase tracking-wider whitespace-nowrap mr-1">Rombel:</span>
    <a href="{{ route('guru.ujian.koreksi', $ujian->id) }}" 
       class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all {{ empty($kelas_id) ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
        Semua Kelas
    </a>
    @foreach($kelasList as $k)
    <a href="{{ route('guru.ujian.koreksi', $ujian->id) }}?kelas_id={{ $k->id }}" 
       class="px-3.5 py-1.5 rounded-xl text-xs font-bold whitespace-nowrap transition-all {{ (isset($kelas_id) && $kelas_id == $k->id) ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
        Kelas {{ $k->nama_kelas }}
    </a>
    @endforeach
</div>
@endif

<form action="{{ route('guru.ujian.koreksi.store', $ujian->id) }}" method="POST">
    @csrf

    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm p-5">
        <div class="flex justify-between items-center pb-3 mb-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-700 text-sm">Lembar Jawaban Essay Siswa ({{ $hasilUjians->count() }} Peserta)</h3>
            <span class="text-xs text-slate-400">Tips: Nilai akhir akan dihitung otomatis secara proporsional.</span>
        </div>
        
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
                <div class="space-y-4 pl-2 mt-3">
                    @foreach($soalEssay as $soalIdx => $soal)
                    @php
                        $jwb = $siswaJawaban->where('soal_id', $soal->id)->first();
                        $hasAnswer = $jwb && trim((string)$jwb->jawaban) !== '';
                        $aiData = $jwb?->aiKoreksi;
                    @endphp
                    <div class="text-xs border-l-2 border-indigo-200 pl-3 py-1" 
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
                        <div class="flex items-center justify-between gap-2 mb-1">
                            <span class="font-bold text-slate-600">Pertanyaan {{ $soalIdx + 1 }} (Bobot: {{ $soal->bobot }}):</span>
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
                        <div class="text-slate-800 font-semibold mb-1.5 leading-relaxed">{!! nl2br(e($soal->pertanyaan)) !!}</div>
                        
                        <div class="font-semibold text-slate-500 mb-0.5">Jawaban Siswa:</div>
                        @if($hasAnswer)
                        <div class="text-slate-800 bg-white p-2.5 rounded-lg border border-slate-200 font-mono whitespace-pre-wrap leading-relaxed">{{ $jwb->jawaban }}</div>

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
